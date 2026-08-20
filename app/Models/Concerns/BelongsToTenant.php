<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to every model whose table is protected by a Row-Level Security
 * policy.
 *
 * Note what this trait deliberately does *not* do: it adds no global scope.
 * Reads are filtered by the database, not by Eloquent. A redundant scope here
 * would paper over a policy that had been dropped or misapplied, and the
 * isolation suite would keep passing while the guarantee was gone.
 *
 * All it does is populate `tenant_id` on insert, so callers are not obliged to
 * repeat the current tenant on every create.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (self $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $tenantId = app(TenantContext::class)->tenantId();

            if ($tenantId !== null) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
