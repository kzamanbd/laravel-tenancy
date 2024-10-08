<?php

use App\Livewire\LoggedInUser;
use App\Livewire\RegisteredUser;
use App\Livewire\TenantManagement;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::view('/', 'welcome');

        Route::middleware(['guest'])->group(function () {
            Route::get('login', LoggedInUser::class)->name('login');

            Route::get('register', RegisteredUser::class)->name('register');
        });

        Route::middleware(['auth'])->group(function () {

            Route::view('dashboard', 'dashboard')->name('dashboard');

            Route::view('profile', 'profile')->name('profile');

            Route::get('tenants', TenantManagement::class)->name('tenants');
        });
    });
}
