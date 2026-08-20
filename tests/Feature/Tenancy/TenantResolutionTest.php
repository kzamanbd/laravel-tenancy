<?php

declare(strict_types=1);

use App\Enums\DomainVerificationStatus;
use App\Models\Component;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

/*
|--------------------------------------------------------------------------
| Tenant resolution over HTTP
|--------------------------------------------------------------------------
|
| Host header -> domain lookup -> tenant -> the PostgreSQL session variable.
| Asserted through a real request rather than by calling the context directly,
| because the middleware ordering is the part that actually breaks.
|
*/

beforeEach(function () {
    [$this->tenantA, $this->tenantB] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);

        return [
            Tenant::factory()->forOrganization($organization)->create(),
            Tenant::factory()->forOrganization($organization)->create(),
        ];
    });

    $this->domainA = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenantA)->create());
    $this->domainB = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenantB)->create());

    // A throwaway endpoint that reports what the request could actually see,
    // so the assertion covers the whole middleware stack.
    Route::middleware(['web', InitializeTenancyByDomain::class])
        ->get('/_tenancy-probe', fn () => response()->json([
            'tenant_id' => app(TenantContext::class)->tenantId(),
            'component_ids' => Component::query()->pluck('id')->all(),
        ]));
});

it('resolves the tenant from the host header', function () {
    $this->get("http://{$this->domainA->domain}/_tenancy-probe")
        ->assertOk()
        ->assertJsonPath('tenant_id', $this->tenantA->id);
});

it('scopes the request to only the resolved tenant\'s rows', function () {
    [$mine, $theirs] = asSuperAdmin(fn () => [
        Component::factory()->forTenant($this->tenantA)->create(),
        Component::factory()->forTenant($this->tenantB)->create(),
    ]);

    $this->get("http://{$this->domainA->domain}/_tenancy-probe")
        ->assertOk()
        ->assertJsonPath('component_ids', [$mine->id]);

    $this->get("http://{$this->domainB->domain}/_tenancy-probe")
        ->assertOk()
        ->assertJsonPath('component_ids', [$theirs->id]);
});

it('rejects a host that belongs to no tenant', function () {
    $this->get('http://unclaimed.example.com/_tenancy-probe')
        ->assertNotFound();
});

it('issues a verification token for a customer owned domain', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenantA)->custom()->create());

    expect($domain->isCustom())->toBeTrue()
        ->and($domain->verification_token)->toStartWith('status-verify-')
        ->and($domain->verification_status)->toBe(DomainVerificationStatus::Pending);
});

it('does not tokenise a platform subdomain', function () {
    expect($this->domainA->isCustom())->toBeFalse()
        ->and($this->domainA->verification_token)->toBeNull();
});

it('refuses to issue a certificate before ownership is proven', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenantA)->custom()->create());

    expect($domain->mayIssueCertificate())->toBeFalse();

    $domain->markVerified();

    expect($domain->fresh()->mayIssueCertificate())->toBeTrue();
});

it('refuses to issue a certificate after verification fails', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenantA)->custom()->create());

    $domain->markVerificationFailed();

    expect($domain->fresh()->mayIssueCertificate())->toBeFalse();
});
