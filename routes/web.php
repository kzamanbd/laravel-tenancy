<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Workspace\ComponentController;
use App\Http\Controllers\Workspace\IncidentController;
use App\Http\Controllers\Workspace\IncidentUpdateController;
use App\Http\Controllers\Workspace\MaintenanceController;
use App\Http\Middleware\EnsureUserBelongsToTenant;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
| Universal routes resolve on BOTH the central domain and any tenant domain.
| On a tenant domain the tenant is initialized; on a central domain the
| UniversalRoutes feature lets the request through without a tenant.
*/
Route::middleware(['universal', InitializeTenancyByDomain::class])->group(function () {
    Route::inertia('/', 'welcome')->name('home');

    Route::middleware(['auth', 'verified'])->group(function () {
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
| Lives on the central domain so an agency can move between client pages in one
| session, with the tenant taken from the path. `{tenant}` must be the first
| route parameter -- InitializeTenancyByPath refuses to resolve otherwise, and
| forgets the parameter once it has, so the controllers never receive it.
|
| InitializeTenancyByPath performs no authorization: it initializes whatever id
| appears in the URL, and Row-Level Security then scopes the request to exactly
| that tenant. EnsureUserBelongsToTenant is what makes that safe, so it must
| stay immediately behind it.
*/
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)
        ->middleware(['auth', 'verified', InitializeTenancyByPath::class, EnsureUserBelongsToTenant::class])
        ->prefix('workspaces/{tenant}')
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

            Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
            Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
            Route::put('maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
            Route::delete('maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])
                ->name('maintenance.destroy');
        });
}

require __DIR__.'/settings.php';
