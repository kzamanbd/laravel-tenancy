<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreDomainRequest;
use App\Models\Domain;
use App\Models\Tenant;
use App\Services\Dns\DomainVerifier;
use App\Services\StatusPagePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Connecting a customer's own hostname to a status page.
 *
 * Adding a domain never issues anything by itself. It records the hostname and
 * hands back the DNS records that would prove ownership; only once DNS agrees
 * does `TlsAskController` start answering yes for it.
 */
class DomainController extends Controller
{
    public function index(DomainVerifier $verifier): Response
    {
        Gate::authorize('viewAny', Domain::class);

        /** @var Tenant $tenant */
        $tenant = tenant();
        $plan = $tenant->organization?->plan;

        return Inertia::render('workspace/domains', [
            'domains' => Domain::query()
                ->where('tenant_id', $tenant->getTenantKey())
                ->orderByDesc('is_primary')
                ->orderBy('domain')
                ->get()
                ->map(fn (Domain $domain): array => [
                    'id' => $domain->id,
                    'domain' => $domain->domain,
                    'isCustom' => $domain->isCustom(),
                    'isPrimary' => $domain->is_primary,
                    'status' => $domain->verification_status->value,
                    'statusLabel' => $domain->verification_status->label(),
                    'verifiedAt' => $domain->verified_at?->toIso8601String(),
                    'lastCheckedAt' => $domain->last_checked_at?->toIso8601String(),
                    'certificateExpiresAt' => $domain->certificate_expires_at?->toIso8601String(),
                    'instructions' => $domain->isCustom() ? $verifier->instructionsFor($domain) : null,
                ]),
            'plan' => [
                'name' => $plan?->label(),
                'allowsCustomDomain' => $plan?->allowsCustomDomain() ?? false,
            ],
            'can' => ['manage' => Gate::allows('create', Domain::class)],
        ]);
    }

    public function store(StoreDomainRequest $request): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        if (! ($tenant->organization?->plan->allowsCustomDomain() ?? false)) {
            return back()->withErrors([
                'domain' => __('Custom domains are not included in your plan.'),
            ]);
        }

        Domain::create([
            'tenant_id' => $tenant->getTenantKey(),
            'domain' => $request->validated('domain'),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Domain added. Add the DNS record, then verify it.'),
        ]);

        return back();
    }

    /**
     * Re-check DNS for a domain.
     *
     * Rate limited per domain: each attempt is an outbound DNS lookup, and a
     * customer waiting for propagation will hammer this button.
     */
    public function verify(Domain $domain, DomainVerifier $verifier, StatusPagePublisher $publisher): RedirectResponse
    {
        Gate::authorize('update', $domain);
        $this->assertBelongsToCurrentTenant($domain);

        $key = 'verify-domain:'.$domain->id;

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 10)) {
            return back()->withErrors([
                'domain' => __('Too many verification attempts. Try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, decaySeconds: 300);

        if (! $verifier->verify($domain)) {
            return back()->withErrors([
                'domain' => __('We could not find the expected DNS record yet. It can take a few minutes to propagate.'),
            ]);
        }

        // A newly verified hostname needs a page to serve, and the host->tenant
        // pointer that the public read path resolves through.
        $publisher->publish(tenant());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Domain verified.')]);

        return back();
    }

    public function makePrimary(Domain $domain): RedirectResponse
    {
        Gate::authorize('update', $domain);
        $this->assertBelongsToCurrentTenant($domain);

        abort_unless($domain->isVerified(), 422);

        // Both writes go through the query builder rather than the model: the
        // instance was loaded before the reset below, so setting the attribute
        // it already holds would mark nothing dirty and save nothing.
        Domain::query()
            ->where('tenant_id', $domain->tenant_id)
            ->update(['is_primary' => false]);

        Domain::query()
            ->whereKey($domain->getKey())
            ->update(['is_primary' => true]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Primary domain updated.')]);

        return back();
    }

    public function destroy(Domain $domain, StatusPagePublisher $publisher): RedirectResponse
    {
        Gate::authorize('delete', $domain);
        $this->assertBelongsToCurrentTenant($domain);

        $domain->delete();

        // Republish so the removed hostname stops resolving to this page.
        $publisher->publish(tenant());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Domain removed.')]);

        return back();
    }

    /**
     * `domains` is a central table, so Row-Level Security does not scope it.
     * Without this check the workspace routes would happily operate on another
     * organization's hostname.
     */
    private function assertBelongsToCurrentTenant(Domain $domain): void
    {
        abort_unless($domain->tenant_id === tenant()->getTenantKey(), 404);
    }
}
