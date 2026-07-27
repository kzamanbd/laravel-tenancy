<?php

use App\Models\Tenant;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register and get their own tenant', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'acme',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull();
    expect(Tenant::count())->toBe(1);
    expect($user->tenants()->count())->toBe(1);

    $baseDomain = config('tenancy.central_domains')[0];
    $this->assertDatabaseHas('domains', ['domain' => "acme.{$baseDomain}"]);

    $protocol = request()->isSecure() ? 'https' : 'http';
    $response->assertRedirect("{$protocol}://acme.{$baseDomain}/dashboard");
});

test('registration requires a unique subdomain', function () {
    $tenant = Tenant::create(['name' => 'Existing']);
    $baseDomain = config('tenancy.central_domains')[0];
    $tenant->createDomain("acme.{$baseDomain}");

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'acme',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('subdomain');
    $this->assertGuest();
});
