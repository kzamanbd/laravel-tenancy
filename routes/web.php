<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Workspace\ComponentController;
use App\Http\Controllers\Workspace\DomainController;
use App\Http\Controllers\Workspace\IncidentController;
use App\Http\Controllers\Workspace\IncidentUpdateController;
use App\Http\Controllers\Workspace\MaintenanceController;
use App\Http\Controllers\Workspace\PageSettingsController;
use App\Http\Controllers\Workspace\SubscriberController;
use App\Http\Middleware\EnsureUserBelongsToTenant;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

/*
| Universal routes resolve on BOTH the central domain and any tenant domain.
| On a tenant domain the tenant is initialized; on a central domain the
| UniversalRoutes feature lets the request through without a tenant.
*/
Route::middleware(['universal', InitializeTenancyByDomain::class])->group(function () {
    Route::inertia('/', 'welcome')->name('home');

    /*
    | The dashboard reports on whichever tenant the host resolved to, so it
    | needs the same membership check the workspace does. Without it, any
    | authenticated user could read another organization's component counts,
    | open incidents, and subscriber totals simply by typing their subdomain --
    | Row-Level Security would scope the queries to that tenant and hand the
    | data over, exactly as asked.
    |
    | On a central domain no tenant resolves, the gate passes through, and the
    | dashboard falls back to the user's own portfolio.
    */
    Route::middleware(['auth', 'verified', EnsureUserBelongsToTenant::class])->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });
});

/*
| Tenant management lives only on the central domain(s).
*/
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->middleware(['auth', 'verified'])->group(function () {
        Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
    });
}

/*
| Workspace administration.
|
| Lives on the tenant's own domain, so the tenant is taken from the host and
| never appears in the path: `acme.example.com/workspaces/components` rather
| than a page id anyone could edit in the address bar.
|
| InitializeTenancyByDomain resolves the tenant and Row-Level Security then
| scopes every query to it. That is identification, not authorization -- it
| will happily resolve a tenant the visitor has nothing to do with, and the
| database will faithfully serve that tenant's rows. EnsureUserBelongsToTenant
| is what makes it safe, so it must stay immediately behind it.
|
| On a central domain the tenant cannot be identified, tenancy throws, and the
| exception handler answers 404 -- these routes simply do not exist there.
*/
Route::middleware(['auth', 'verified', InitializeTenancyByDomain::class, EnsureUserBelongsToTenant::class])
    ->prefix('workspaces')
    ->name('workspace.')
    ->group(function () {
        Route::get('components', [ComponentController::class, 'index'])->name('components.index');
        Route::post('components', [ComponentController::class, 'store'])->name('components.store');
        Route::put('components/{component}', [ComponentController::class, 'update'])->name('components.update');
        Route::delete('components/{component}', [ComponentController::class, 'destroy'])->name('components.destroy');

        Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
        Route::post('incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::get('incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
        Route::put('incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
        Route::delete('incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');

        Route::post('incidents/{incident}/updates', [IncidentUpdateController::class, 'store'])
            ->name('incidents.updates.store');
        Route::post('incidents/{incident}/updates/{update}/publish', [IncidentUpdateController::class, 'publish'])
            ->name('incidents.updates.publish');
        Route::delete('incidents/{incident}/updates/{update}', [IncidentUpdateController::class, 'destroy'])
            ->name('incidents.updates.destroy');

        Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
        Route::post('subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
        Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])
            ->name('subscribers.destroy');

        Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
        Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
        Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])->name('domains.verify');
        Route::post('domains/{domain}/primary', [DomainController::class, 'makePrimary'])->name('domains.primary');
        Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

        Route::get('settings', [PageSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [PageSettingsController::class, 'update'])->name('settings.update');
        Route::post('publish', [PageSettingsController::class, 'publish'])->name('publish');

        Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::put('maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
        Route::delete('maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])
            ->name('maintenance.destroy');
    });

require __DIR__.'/settings.php';
