<?php

namespace App\Http\Routes;

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

class SharedRoutes
{
    /**
     * Register common authentication routes
     * This can be included in both central and tenant route files
     */
    public static function register(): void
    {
        // Guest routes (authentication)
        Route::middleware('guest')->group(function () {
            Route::get('login', Login::class)->name('login');
            Route::get('register', Register::class)->name('register');
            Route::get('forgot-password', ForgotPassword::class)->name('password.request');
            Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');
        });

        // Authenticated routes
        Route::middleware('auth')->group(function () {
            // Dashboard
            Route::view('dashboard', 'dashboard')
                ->middleware('verified')
                ->name('dashboard');

            // Settings routes
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::redirect('/', 'settings/profile');
                Route::get('profile', Profile::class)->name('profile');
                Route::get('password', Password::class)->name('password');
                Route::get('appearance', Appearance::class)->name('appearance');
            });

            // Email verification routes
            Route::get('verify-email', VerifyEmail::class)->name('verification.notice');
            Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

            // Password confirmation
            Route::get('confirm-password', ConfirmPassword::class)->name('password.confirm');

            // Logout
            Route::post('logout', \App\Livewire\Actions\Logout::class)->name('logout');
        });
    }
}
