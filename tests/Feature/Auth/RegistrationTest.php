<?php

use App\Http\Responses\RegisterResponse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

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

test('an inertia registration is sent to the tenant domain as a location visit', function () {
    // The redirect crosses origins: registration happens on the central domain
    // and the dashboard lives on the tenant's subdomain. An Inertia XHR cannot
    // follow that, so the response must be a 409 telling the client to perform
    // a full window.location visit. A 302 here means the browser silently
    // strands the user on the register form.
    $response = $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => ''])
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'inertia@example.com',
            'subdomain' => 'inertia-co',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $baseDomain = config('tenancy.central_domains')[0];
    $protocol = request()->isSecure() ? 'https' : 'http';

    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', "{$protocol}://inertia-co.{$baseDomain}/dashboard");
    $this->assertAuthenticated();
});

test('a same-origin registration redirect stays an ordinary redirect', function () {
    // Nothing crosses origins when the target host matches, so the normal
    // Inertia redirect must survive -- the 409 is for the cross-origin case
    // only.
    $baseDomain = config('tenancy.central_domains')[0];

    $request = Request::create("http://{$baseDomain}/register", 'POST', server: [
        'HTTP_X_INERTIA' => 'true',
    ]);
    $request->setLaravelSession(session()->driver());

    session(['registered_tenant_url' => "http://{$baseDomain}/dashboard"]);

    $response = app(RegisterResponse::class)->toResponse($request);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toBe("http://{$baseDomain}/dashboard");
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
