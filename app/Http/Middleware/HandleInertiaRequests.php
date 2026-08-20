<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user()?->load('tenants.domains');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                // Drives the sidebar's status-page switcher. Central table, so
                // it reads without a tenant resolved.
                'tenants' => $user
                    ? $user->tenants
                        ->map(fn ($tenant): array => [
                            'id' => $tenant->getTenantKey(),
                            'name' => $tenant->name,
                            'slug' => $tenant->slug,
                            'domain' => $tenant->domains->first()?->domain,
                        ])
                        ->all()
                    : [],
            ],
            'tenant' => tenant() ? [
                'id' => tenant('id'),
                'name' => tenant('name'),
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
