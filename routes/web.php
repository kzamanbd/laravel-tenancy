<?php

use App\Livewire\LoggedInUser;
use App\Livewire\TenantManagement;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::view('/', 'welcome');
        Route::get('login', LoggedInUser::class)
            ->middleware('guest')
            ->name('login');

        Route::middleware(['auth'])->group(function () {

            Route::view('dashboard', 'dashboard')
                ->middleware(['verified'])
                ->name('dashboard');

            Route::view('profile', 'profile')
                ->name('profile');

            Route::get('tenants', TenantManagement::class)->name('tenants');
        });
    });
}
