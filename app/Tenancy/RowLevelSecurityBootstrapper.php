<?php

declare(strict_types=1);

namespace App\Tenancy;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Ties the RLS session variable to the tenancy lifecycle, so that initializing
 * tenancy by domain is the same act as constraining the database.
 *
 * Registered in `config/tenancy.php` under `bootstrappers`.
 */
class RowLevelSecurityBootstrapper implements TenancyBootstrapper
{
    public function __construct(private TenantContext $context) {}

    public function bootstrap(Tenant $tenant)
    {
        $this->context->apply((int) $tenant->getTenantKey());
    }

    public function revert()
    {
        $this->context->forget();
    }
}
