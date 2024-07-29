<?php

use App\Livewire\TenantManagement;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->middleware(['verified'])
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->name('profile');

    Route::get('tenants', TenantManagement::class)->name('tenants');
});

require __DIR__ . '/auth.php';
