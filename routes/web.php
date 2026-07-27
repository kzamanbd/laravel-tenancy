<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

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

require __DIR__.'/settings.php';
