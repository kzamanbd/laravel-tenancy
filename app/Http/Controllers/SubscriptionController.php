<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SubscriberChannel;
use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Subscribing to a status page from the published page itself.
 *
 * The page is a static file on a CDN, so the form posts back here. Reads stay
 * on the CDN; only the write comes to the application.
 *
 * These routes carry no session, which also means no CSRF token -- a published
 * file cannot hold a fresh one, and a stale one would only ever reject real
 * readers. There is nothing for a forged request to ride: an anonymous caller
 * can already reach this endpoint directly, and all it can achieve is sending
 * one confirmation link to an address that must then act on it. IP throttling
 * is what limits abuse here.
 *
 * Nothing is ever delivered to an address that has not confirmed. Every tenant
 * sends through the same reputation, so one page harvesting addresses and
 * mailing them unasked would degrade deliverability for everyone -- which is
 * why double opt-in is enforced in the model rather than left to the caller.
 */
class SubscriptionController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function store(Request $request, string $tenant): View
    {
        $page = $this->pageOrFail($tenant);

        try {
            $validated = Validator::make($request->all(), [
                // Format only, deliberately: the `dns` rule performs a live MX
                // lookup, which makes an anonymous endpoint depend on a resolver
                // and lets a slow lookup hold the request open. Domains that do not
                // accept mail are caught by bounce handling, which has to exist
                // regardless.
                'email' => ['required', 'email:rfc', 'max:255'],
                'component_ids' => ['array'],
                'component_ids.*' => ['integer'],
            ])->validate();
        } catch (ValidationException $exception) {
            // There is no session to flash errors into: the form was submitted
            // from a static file that may not even be on this domain.
            return view('status-page.subscription', [
                'page' => $page,
                'heading' => 'That address did not look right',
                'message' => $exception->validator->errors()->first('email'),
            ]);
        }

        $this->context->withoutIsolation(function () use ($page, $validated): void {
            $existing = Subscriber::query()
                ->where('tenant_id', $page->getTenantKey())
                ->where('channel', SubscriberChannel::Email)
                ->where('endpoint', $validated['email'])
                ->first();

            // Re-subscribing an address that already confirmed must not send
            // anything: otherwise the form becomes a way to mail a stranger
            // repeatedly by typing their address.
            if ($existing !== null && $existing->isDeliverable()) {
                return;
            }

            // Plan limits are enforced here rather than at delivery: refusing
            // a sign-up is honest, whereas accepting an address and silently
            // never mailing it is not.
            if ($existing === null && $this->atSubscriberLimit($page)) {
                return;
            }

            $subscriber = $existing ?? new Subscriber([
                'channel' => SubscriberChannel::Email,
                'endpoint' => $validated['email'],
            ]);

            $subscriber->tenant_id = $page->getTenantKey();
            $subscriber->component_ids = $validated['component_ids'] ?? null;
            $subscriber->unsubscribed_at = null;
            $subscriber->save();

            Mail::to($subscriber->endpoint)->send(new ConfirmSubscription($page, $subscriber));
        });

        // The same response either way, so the form cannot be used to test
        // whether an address is already subscribed to a page.
        return view('status-page.subscription', [
            'page' => $page,
            'heading' => 'Check your email',
            'message' => "If that address can be subscribed, we have sent it a confirmation link for {$page->name}.",
        ]);
    }

    public function confirm(string $token): View
    {
        $subscriber = $this->context->withoutIsolation(
            fn (): ?Subscriber => Subscriber::query()->where('confirmation_token', $token)->first(),
        );

        abort_if($subscriber === null, 404);

        $page = $this->context->withoutIsolation(
            fn (): ?Tenant => Tenant::query()->find($subscriber->tenant_id),
        );

        abort_if($page === null, 404);

        $this->context->withoutIsolation(function () use ($subscriber): void {
            $subscriber->forceFill([
                'confirmed_at' => $subscriber->confirmed_at ?? now(),
                'confirmation_token' => null,
                'unsubscribed_at' => null,
            ])->save();
        });

        return view('status-page.subscription', [
            'page' => $page,
            'heading' => 'Subscription confirmed',
            'message' => "You will now receive updates for {$page->name}.",
        ]);
    }

    /**
     * One click, no login, no confirmation step.
     *
     * Anything slower than this gets reported as spam instead, and a spam
     * complaint costs every tenant on the shared reputation.
     */
    public function unsubscribe(string $token): View
    {
        $subscriber = $this->context->withoutIsolation(
            fn (): ?Subscriber => Subscriber::query()->where('unsubscribe_token', $token)->first(),
        );

        abort_if($subscriber === null, 404);

        $page = $this->context->withoutIsolation(
            fn (): ?Tenant => Tenant::query()->find($subscriber->tenant_id),
        );

        $this->context->withoutIsolation(function () use ($subscriber): void {
            $subscriber->forceFill(['unsubscribed_at' => now()])->save();
        });

        return view('status-page.subscription', [
            'page' => $page,
            'heading' => 'Unsubscribed',
            'message' => 'You will no longer receive updates from this status page.',
        ]);
    }

    private function atSubscriberLimit(Tenant $page): bool
    {
        $limit = $page->organization?->plan->subscriberLimit();

        if ($limit === null) {
            return false;
        }

        return Subscriber::query()
            ->where('tenant_id', $page->getTenantKey())
            ->whereNull('unsubscribed_at')
            ->count() >= $limit;
    }

    private function pageOrFail(string $tenant): Tenant
    {
        abort_unless(ctype_digit($tenant), 404);

        $page = $this->context->withoutIsolation(
            fn (): ?Tenant => Tenant::query()->find((int) $tenant),
        );

        abort_if($page === null, 404);

        return $page;
    }
}
