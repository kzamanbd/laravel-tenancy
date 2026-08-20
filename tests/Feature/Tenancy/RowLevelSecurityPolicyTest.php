<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Policy wiring
|--------------------------------------------------------------------------
|
| The isolation guarantee rests on three things being simultaneously true:
| the connecting role cannot bypass policies, every tenant-scoped table has
| RLS enabled *and* forced, and each carries an isolation policy. Any one of
| them silently regressing would leave the leak suite passing against an
| unprotected database, so they are asserted directly.
|
*/

/**
 * @return list<string>
 */
function tenantScopedTables(): array
{
    return [
        'component_groups',
        'components',
        'incidents',
        'incident_updates',
        'component_incident',
        'maintenances',
        'component_maintenance',
        'subscribers',
        'monitors',
    ];
}

it('connects as a role that cannot bypass row level security', function () {
    $role = DB::selectOne(
        'SELECT rolsuper, rolbypassrls FROM pg_roles WHERE rolname = current_user'
    );

    expect($role->rolsuper)->toBeFalse()
        ->and($role->rolbypassrls)->toBeFalse();
});

it('has row level security enabled and forced on every tenant scoped table', function (string $table) {
    $state = DB::selectOne(
        'SELECT relrowsecurity, relforcerowsecurity FROM pg_class WHERE relname = ?',
        [$table]
    );

    expect($state)->not->toBeNull("Table [{$table}] does not exist.")
        ->and($state->relrowsecurity)->toBeTrue("RLS is not enabled on [{$table}].")
        ->and($state->relforcerowsecurity)->toBeTrue(
            "RLS is not FORCED on [{$table}]; the table owner would bypass it."
        );
})->with(tenantScopedTables());

it('has an isolation policy on every tenant scoped table', function (string $table) {
    $policies = DB::select(
        'SELECT policyname FROM pg_policies WHERE tablename = ?',
        [$table]
    );

    expect($policies)->toHaveCount(1)
        ->and($policies[0]->policyname)->toBe('tenant_isolation');
})->with(tenantScopedTables());

it('covers every table carrying a tenant_id column', function () {
    $columns = DB::select(<<<'SQL'
        SELECT table_name
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND column_name = 'tenant_id'
        SQL);

    $withTenantId = collect($columns)->pluck('table_name')->sort()->values();

    // `memberships` and `domains` are central: they are how a user is granted
    // access to a tenant in the first place, so they cannot be gated behind
    // having already selected one.
    $central = ['domains', 'memberships', 'tenant_user'];

    $expected = collect(tenantScopedTables())->merge($central)->sort()->values();

    expect($withTenantId->all())->toEqual($expected->all(),
        'A table gained a tenant_id column without a matching RLS policy.');
});
