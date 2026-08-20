<?php

declare(strict_types=1);

use App\Models\Domain;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\Dns\DnsResolver;
use Tests\Support\FakeDnsResolver;

/*
|--------------------------------------------------------------------------
| Renewal monitoring
|--------------------------------------------------------------------------
|
| Certificates renew on their own, but only while the customer's DNS still
| points here. When it stops, nothing breaks until the certificate expires and
| every visitor gets a browser warning -- which is precisely the failure that
| generates support tickets forever.
|
*/

beforeEach(function () {
    $this->dns = new FakeDnsResolver;
    $this->app->instance(DnsResolver::class, $this->dns);

    $this->tenant = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());
});

it('reports a verified domain that still resolves here as healthy', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()->create());

    $this->dns->setCname($domain->domain, $domain->expectedCnameTarget());

    $this->artisan('domains:check')
        ->expectsOutputToContain($domain->domain)
        ->assertSuccessful();

    expect(asSuperAdmin(fn () => $domain->fresh())->last_checked_at)->not->toBeNull();
});

it('flags a domain whose DNS no longer points here', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()->create());

    $this->artisan('domains:check')
        ->expectsOutputToContain('no longer')
        ->assertSuccessful();
});

it('does not revoke verification on a single failed lookup', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()->create());

    $this->artisan('domains:check')->assertSuccessful();

    // Pulling the certificate over one transient resolver failure would take a
    // customer's page offline for a blip. Record it; let a human decide.
    expect(asSuperAdmin(fn () => $domain->fresh())->isVerified())->toBeTrue();
});

it('warns about a certificate close to expiry', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()
        ->create(['certificate_expires_at' => now()->addDays(3)]));

    $this->dns->setCname($domain->domain, $domain->expectedCnameTarget());

    $this->artisan('domains:check')
        ->expectsOutputToContain('expires')
        ->assertSuccessful();
});

it('ignores platform subdomains, which we control', function () {
    asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->verified()->create());

    $this->artisan('domains:check')
        ->expectsOutputToContain('No custom domains')
        ->assertSuccessful();
});

it('ignores domains that were never verified', function () {
    asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->create());

    $this->artisan('domains:check')
        ->expectsOutputToContain('No custom domains')
        ->assertSuccessful();
});
