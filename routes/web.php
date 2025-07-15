<?php

declare(strict_types=1);

use App\Http\Routes\SharedRoutes;
use App\Livewire\TenantManagement;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // Public routes
        Route::view('/', 'welcome')->name('home');

        // Authenticated routes
        Route::middleware('auth')->group(function () {
            // Dashboard
            Route::view('dashboard', 'dashboard')->name('dashboard');
            // Tenant management (only on central domain)
            Route::get('tenants', TenantManagement::class)->name('tenants');
        });

        // Shared routes
        SharedRoutes::register();
    });
}
