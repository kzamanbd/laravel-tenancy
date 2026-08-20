<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\ComponentGroup;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Maintenance;
use App\Models\Monitor;
use App\Models\Organization;
use App\Models\Subscriber;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| The leak test suite
|--------------------------------------------------------------------------
|
| Authenticates as tenant A and attempts every read and write it can reach
| against tenant B's primary keys. Nothing here asserts that the application
| filters correctly -- it asserts that the database refuses, which is what
| makes the guarantee survive a forgotten `where` clause.
|
| If anything else on the roadmap gets cut, this suite does not.
|
*/

/**
 * Every tenant-scoped model, with a factory call that seeds one row for a
 * given tenant. Adding a model here is what extends the attack surface the
 * suite covers.
 *
 * @return list<class-string<Model>>
 */
function tenantScopedModels(): array
{
    return [
        ComponentGroup::class,
        Component::class,
        Incident::class,
        IncidentUpdate::class,
        Maintenance::class,
        Subscriber::class,
        Monitor::class,
    ];
}

/**
 * @param  class-string<Model>  $model
 */
function seedRowFor(string $model, Tenant $tenant): Model
{
    return match ($model) {
        IncidentUpdate::class => IncidentUpdate::factory()->forTenant($tenant)->create([
            'incident_id' => Incident::factory()->forTenant($tenant)->create()->id,
        ]),
        default => $model::factory()->forTenant($tenant)->create(),
    };
}

/**
 * A second row for the same model that belongs to the *other* tenant, used to
 * forge a cross-tenant write.
 *
 * @param  class-string<Model>  $model
 * @return array<string, mixed>
 */
function forgedAttributesFor(string $model, Tenant $victim): array
{
    $row = asSuperAdmin(fn () => seedRowFor($model, $victim));

    $attributes = $row->getAttributes();
    unset($attributes['id'], $attributes['created_at'], $attributes['updated_at']);

    asSuperAdmin(fn () => $row->delete());

    return $attributes;
}

beforeEach(function () {
    [$this->organization, $this->tenantA, $this->tenantB] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);

        return [
            $organization,
            Tenant::factory()->forOrganization($organization)->create(['name' => 'Tenant A']),
            Tenant::factory()->forOrganization($organization)->create(['name' => 'Tenant B']),
        ];
    });
});

it('hides another tenant\'s rows from a listing', function (string $model) {
    [$mine, $theirs] = asSuperAdmin(fn () => [
        seedRowFor($model, $this->tenantA),
        seedRowFor($model, $this->tenantB),
    ]);

    actingAsTenant($this->tenantA);

    $visible = $model::query()->pluck('id');

    expect($visible)->toContain($mine->id)
        ->and($visible)->not->toContain($theirs->id);
})->with(tenantScopedModels());

it('cannot fetch another tenant\'s row by its primary key', function (string $model) {
    $theirs = asSuperAdmin(fn () => seedRowFor($model, $this->tenantB));

    actingAsTenant($this->tenantA);

    expect($model::find($theirs->id))->toBeNull()
        ->and($model::where('id', $theirs->id)->exists())->toBeFalse()
        ->and($model::where('id', $theirs->id)->count())->toBe(0);
})->with(tenantScopedModels());

it('cannot update another tenant\'s row', function (string $model) {
    $theirs = asSuperAdmin(fn () => seedRowFor($model, $this->tenantB));

    actingAsTenant($this->tenantA);

    $affected = $model::where('id', $theirs->id)->update(['updated_at' => now()]);

    expect($affected)->toBe(0);

    $stillThere = asSuperAdmin(fn () => $model::find($theirs->id));

    expect($stillThere)->not->toBeNull();
})->with(tenantScopedModels());

it('cannot delete another tenant\'s row', function (string $model) {
    $theirs = asSuperAdmin(fn () => seedRowFor($model, $this->tenantB));

    actingAsTenant($this->tenantA);

    $deleted = $model::where('id', $theirs->id)->delete();

    expect($deleted)->toBe(0);

    $survived = asSuperAdmin(fn () => $model::find($theirs->id));

    expect($survived)->not->toBeNull();
})->with(tenantScopedModels());

it('cannot forge a row belonging to another tenant', function (string $model) {
    $attributes = forgedAttributesFor($model, $this->tenantB);

    actingAsTenant($this->tenantA);

    expect(fn () => $model::query()->insert($attributes))
        ->toThrow(QueryException::class);
})->with(tenantScopedModels());

it('cannot reassign its own row to another tenant', function (string $model) {
    $mine = asSuperAdmin(fn () => seedRowFor($model, $this->tenantA));

    actingAsTenant($this->tenantA);

    expect(fn () => $model::where('id', $mine->id)->update(['tenant_id' => $this->tenantB->id]))
        ->toThrow(QueryException::class);
})->with(tenantScopedModels());

it('leaks nothing through the raw query builder', function (string $model) {
    $theirs = asSuperAdmin(fn () => seedRowFor($model, $this->tenantB));

    actingAsTenant($this->tenantA);

    $table = (new $model)->getTable();

    expect(DB::table($table)->where('id', $theirs->id)->first())->toBeNull()
        ->and(DB::table($table)->where('tenant_id', $this->tenantB->id)->count())->toBe(0);
})->with(tenantScopedModels());

it('leaks nothing through aggregates', function (string $model) {
    asSuperAdmin(function () use ($model) {
        seedRowFor($model, $this->tenantA);
        seedRowFor($model, $this->tenantB);
        seedRowFor($model, $this->tenantB);
    });

    actingAsTenant($this->tenantA);

    expect($model::count())->toBe(1)
        ->and($model::max('id'))->toBe($model::query()->value('id'));
})->with(tenantScopedModels());

it('fails closed when no tenant has been resolved', function (string $model) {
    asSuperAdmin(function () use ($model) {
        seedRowFor($model, $this->tenantA);
        seedRowFor($model, $this->tenantB);
    });

    actingAsCentralDomain();

    expect($model::count())->toBe(0);
})->with(tenantScopedModels());

it('stops seeing a tenant\'s rows once tenancy ends', function () {
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenantA)->create());

    actingAsTenant($this->tenantA);
    expect(Component::count())->toBe(1);

    actingAsCentralDomain();
    expect(Component::count())->toBe(0);
});

it('swaps visibility when switching between tenants', function () {
    [$a, $b] = asSuperAdmin(fn () => [
        Component::factory()->forTenant($this->tenantA)->create(),
        Component::factory()->forTenant($this->tenantB)->create(),
    ]);

    actingAsTenant($this->tenantA);
    expect(Component::pluck('id')->all())->toBe([$a->id]);

    actingAsTenant($this->tenantB);
    expect(Component::pluck('id')->all())->toBe([$b->id]);
});
