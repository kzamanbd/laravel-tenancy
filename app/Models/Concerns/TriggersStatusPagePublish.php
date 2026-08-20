<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Jobs\PublishStatusPage;
use App\Models\Tenant;
use App\Tenancy\TenantContext;

/**
 * Queues a republish whenever a record that appears on the public page changes.
 *
 * Applied to the models the page is built from, so nobody has to remember to
 * republish after an edit. Forgetting is exactly the failure this product
 * exists to prevent -- a page that only updates when someone remembers is the
 * problem, not the fix.
 */
trait TriggersStatusPagePublish
{
    public static function bootTriggersStatusPagePublish(): void
    {
        $queue = function (self $model): void {
            $tenantId = $model->getAttribute('tenant_id');

            if ($tenantId === null) {
                return;
            }

            // The tenant row is central, but a queue worker or console command
            // has no tenant resolved, so look it up without isolation.
            $tenant = app(TenantContext::class)->withoutIsolation(
                fn (): ?Tenant => Tenant::query()->find($tenantId),
            );

            if ($tenant === null) {
                return;
            }

            PublishStatusPage::dispatch($tenant);
        };

        static::saved($queue);
        static::deleted($queue);
    }
}
