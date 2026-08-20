<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\Organization;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Tenant context and the escape hatch
|--------------------------------------------------------------------------
|
| `withoutIsolation()` is the one sanctioned way past the policies. Because it
| is the only way, its behaviour under nesting and exceptions is part of the
| isolation guarantee rather than an implementation detail.
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

    $this->context = app(TenantContext::class);
});

it('starts with no tenant resolved', function () {
    actingAsCentralDomain();

    expect($this->context->hasTenant())->toBeFalse()
        ->and($this->context->tenantId())->toBeNull();
});

it('records the tenant that tenancy resolved', function () {
    actingAsTenant($this->tenantA);

    expect($this->context->tenantId())->toBe($this->tenantA->id)
        ->and($this->context->hasTenant())->toBeTrue();
});

it('writes the tenant into the postgres session variable the policies read', function () {
    actingAsTenant($this->tenantA);

    $value = DB::selectOne(
        'SELECT current_setting(?, true) AS value',
        [TenantContext::TENANT_KEY]
    )->value;

    expect($value)->toBe((string) $this->tenantA->id);
});

it('clears the session variable when tenancy ends', function () {
    actingAsTenant($this->tenantA);
    actingAsCentralDomain();

    $value = DB::selectOne(
        'SELECT current_setting(?, true) AS value',
        [TenantContext::TENANT_KEY]
    )->value;

    expect($value)->toBe('');
});

it('stamps the current tenant onto new records automatically', function () {
    actingAsTenant($this->tenantA);

    $component = Component::create(['name' => 'Checkout API']);

    expect($component->tenant_id)->toBe($this->tenantA->id);
});

it('does not override an explicitly set tenant id', function () {
    actingAsTenant($this->tenantA);

    $component = new Component(['name' => 'Explicit']);
    $component->tenant_id = $this->tenantA->id;
    $component->save();

    expect($component->tenant_id)->toBe($this->tenantA->id);
});

it('sees across tenants only inside withoutIsolation', function () {
    asSuperAdmin(function () {
        Component::factory()->forTenant($this->tenantA)->create();
        Component::factory()->forTenant($this->tenantB)->create();
    });

    actingAsTenant($this->tenantA);

    expect(Component::count())->toBe(1);

    $everything = $this->context->withoutIsolation(fn () => Component::count());

    expect($everything)->toBe(2);
});

it('re-arms isolation after the escape hatch returns', function () {
    asSuperAdmin(function () {
        Component::factory()->forTenant($this->tenantA)->create();
        Component::factory()->forTenant($this->tenantB)->create();
    });

    actingAsTenant($this->tenantA);

    $this->context->withoutIsolation(fn () => Component::count());

    expect($this->context->isBypassing())->toBeFalse()
        ->and(Component::count())->toBe(1);
});

it('re-arms isolation even when the escape hatch throws', function () {
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenantB)->create());

    actingAsTenant($this->tenantA);

    expect(fn () => $this->context->withoutIsolation(function () {
        throw new RuntimeException('provisioning blew up');
    }))->toThrow(RuntimeException::class);

    expect($this->context->isBypassing())->toBeFalse()
        ->and(Component::count())->toBe(0);
});

it('restores the outer state when escape hatches nest', function () {
    actingAsTenant($this->tenantA);

    $this->context->withoutIsolation(function () {
        expect($this->context->isBypassing())->toBeTrue();

        $this->context->withoutIsolation(function () {
            expect($this->context->isBypassing())->toBeTrue();
        });

        // The inner call must not disarm the bypass the outer one opened.
        expect($this->context->isBypassing())->toBeTrue();
    });

    expect($this->context->isBypassing())->toBeFalse();
});

it('re-applies the tenant onto a reconnected handle', function () {
    actingAsTenant($this->tenantA);
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenantA)->create());

    // A queue worker reconnecting mid-run gets a fresh session with none of
    // the previous connection's variables set.
    DB::statement('SELECT set_config(?, ?, false)', [TenantContext::TENANT_KEY, '']);
    expect(Component::count())->toBe(0);

    $this->context->refresh();

    expect(Component::count())->toBe(1);
});
