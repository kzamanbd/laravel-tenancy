<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Settings\Profile;
use App\Livewire\TenantManagement;
use App\Livewire\Settings\Password;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::view('/', 'welcome')->name('home');

        Route::middleware(['guest'])->group(function () {
            Route::get('login', Login::class)->name('login');
            Route::get('register', Register::class)->name('register');
            Route::get('forgot-password', ForgotPassword::class)->name('password.request');
            Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');
        });

        Route::middleware(['auth'])->group(function () {
            Route::redirect('settings', 'settings/profile');
            Route::view('dashboard', 'dashboard')->name('dashboard');
            Route::get('settings/profile', Profile::class)->name('settings.profile');
            Route::get('settings/password', Password::class)->name('settings.password');
            Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
            Route::get('tenants', TenantManagement::class)->name('tenants');
        });
        Route::post('logout', App\Livewire\Actions\Logout::class)->name('logout');
    });
}
