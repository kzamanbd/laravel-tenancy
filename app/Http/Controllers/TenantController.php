<?php

namespace App\Http\Controllers;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * List all tenants with their domains and users.
     */
    public function index(): Response
    {
        $tenants = Tenant::query()
            ->with(['domains', 'users:id,name,email'])
            ->get()
            ->map(fn (Tenant $tenant) => [
                'id' => $tenant->getTenantKey(),
                'name' => $tenant->name,
                'domains' => $tenant->domains->pluck('domain'),
                'users' => $tenant->users->map(fn ($user) => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
            ]);

        return Inertia::render('tenants', [
            'tenants' => $tenants,
            'baseDomain' => config('tenancy.central_domains')[0],
        ]);
    }

    /**
     * Create a new tenant and its domain.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:domains,domain'],
        ]);

        $subdomain = Str::slug($validated['subdomain']);
        $domain = $subdomain.'.'.config('tenancy.central_domains')[0];

        DB::transaction(function () use ($request, $validated, $domain) {
            $tenant = Tenant::create(['name' => $validated['name']]);
            $tenant->createDomain($domain);

            // Without this the creator cannot reach the page they just made:
            // workspace routes are gated on membership, and the dashboard lists
            // pages through the same relation.
            $tenant->users()->attach($request->user());

            Membership::create([
                'user_id' => $request->user()->id,
                'tenant_id' => $tenant->id,
                'role' => MembershipRole::Owner,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tenant created.')]);

        return back();
    }
}
