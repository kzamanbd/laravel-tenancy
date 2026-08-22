<?php

declare(strict_types=1);

namespace App\Tenancy;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Owns the PostgreSQL session variables that every Row-Level Security policy
 * reads.
 *
 * Nothing in the application filters rows by tenant. The database does it,
 * which is why forgetting a `where` clause here cannot leak data. The single
 * responsibility of this class is keeping `app.tenant_id` truthful for the
 * lifetime of a request or job.
 */
class TenantContext
{
    /**
     * The PostgreSQL session variable each policy compares `tenant_id` against.
     */
    public const TENANT_KEY = 'app.tenant_id';

    /**
     * The audited escape hatch used by central-domain and super-admin work.
     */
    public const BYPASS_KEY = 'app.bypass_rls';

    private ?int $tenantId = null;

    private bool $bypassing = false;

    /**
     * Bind the connection to a tenant. Every subsequent query on this
     * connection sees that tenant's rows and no others.
     */
    public function apply(int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->setSessionVariable(self::TENANT_KEY, (string) $tenantId);
    }

    /**
     * Drop back to no tenant. This is not a neutral state -- with no tenant
     * set, every policy evaluates to NULL and no tenant-scoped row is visible.
     */
    public function forget(): void
    {
        $this->tenantId = null;
        $this->setSessionVariable(self::TENANT_KEY, '');
    }

    public function tenantId(): ?int
    {
        return $this->tenantId;
    }

    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }

    public function isBypassing(): bool
    {
        return $this->bypassing;
    }

    /**
     * Run a callback with isolation disabled.
     *
     * Legitimate uses are narrow: super-admin consoles, cross-tenant
     * provisioning, and the static publish pipeline. It is deliberately
     * verbose to call, and always restores the prior state -- including when
     * the callback throws.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function withoutIsolation(callable $callback): mixed
    {
        $wasBypassing = $this->bypassing;

        $this->bypassing = true;
        $this->setSessionVariable(self::BYPASS_KEY, 'on');

        try {
            return $callback();
        } finally {
            $this->bypassing = $wasBypassing;
            $this->setSessionVariable(self::BYPASS_KEY, $wasBypassing ? 'on' : 'off');
        }
    }

    /**
     * Re-apply the current state to the connection.
     *
     * Session variables live on the connection, so anything that hands back a
     * fresh or reconnected handle -- a queue worker picking up a job, a
     * reconnect after the server drops the link -- must call this or the
     * policies will silently see no tenant.
     */
    public function refresh(): void
    {
        $this->setSessionVariable(self::TENANT_KEY, (string) ($this->tenantId ?? ''));
        $this->setSessionVariable(self::BYPASS_KEY, $this->bypassing ? 'on' : 'off');
    }

    /**
     * `set_config` is used rather than `SET` so the value can be bound as a
     * parameter instead of interpolated into DDL-shaped SQL.
     */
    private function setSessionVariable(string $key, string $value): void
    {
        $connection = $this->connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $connection->statement('SELECT set_config(?, ?, false)', [$key, $value]);
    }

    private function connection(): Connection
    {
        return DB::connection();
    }
}
