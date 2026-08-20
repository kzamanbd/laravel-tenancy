<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL Row-Level Security over every tenant-scoped table.
 *
 * The asymmetry that decides this design: a forgotten `where tenant_id = ?`
 * in application code fails *open*, leaking one tenant's data to another.
 * A policy evaluated by the database fails *closed* -- when no tenant is set,
 * `current_setting('app.tenant_id', true)` is NULL, the comparison is NULL,
 * and zero rows match.
 *
 * Two details make this real rather than decorative:
 *
 * 1. FORCE ROW LEVEL SECURITY. Policies do not apply to a table's owner by
 *    default, and migrations run as the owner. Without FORCE the application
 *    role would sail straight past every policy.
 * 2. The connecting role must be NOSUPERUSER and NOBYPASSRLS. Superusers and
 *    roles holding BYPASSRLS ignore policies unconditionally. `RlsRoleTest`
 *    asserts this against the live connection.
 *
 * `app.bypass_rls` is a deliberate, auditable escape hatch for central-domain
 * and super-admin work. It is off unless a caller opts in through
 * `TenantContext::withoutIsolation()`.
 */
return new class extends Migration
{
    /**
     * Tables carrying a `tenant_id` that must never be readable across tenants.
     *
     * @var list<string>
     */
    private array $tables = [
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

    public function up(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");

            DB::statement(<<<SQL
                CREATE POLICY tenant_isolation ON {$table}
                    USING (
                        current_setting('app.bypass_rls', true) = 'on'
                        OR tenant_id::text = current_setting('app.tenant_id', true)
                    )
                    WITH CHECK (
                        current_setting('app.bypass_rls', true) = 'on'
                        OR tenant_id::text = current_setting('app.tenant_id', true)
                    )
                SQL);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP POLICY IF EXISTS tenant_isolation ON {$table}");
            DB::statement("ALTER TABLE {$table} NO FORCE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
