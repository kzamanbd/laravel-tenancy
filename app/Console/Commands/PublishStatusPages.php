<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\StatusPagePublisher;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Republishes status pages from the console.
 *
 * Needed for the cases the model hooks cannot cover: a template change, a
 * storage bucket restored from backup, or the first publish of pages that
 * existed before this pipeline did.
 */
class PublishStatusPages extends Command
{
    protected $signature = 'status-page:publish
                            {tenant? : Publish only this tenant id}
                            {--stale : Only pages never published, or changed since their last publish}';

    protected $description = 'Render status pages to object storage';

    public function handle(StatusPagePublisher $publisher, TenantContext $context): int
    {
        $tenants = $context->withoutIsolation(function (): Collection {
            $query = Tenant::query();

            if ($id = $this->argument('tenant')) {
                $query->whereKey($id);
            }

            if ($this->option('stale')) {
                $query->where(function ($query): void {
                    $query->whereNull('last_published_at')
                        ->orWhereColumn('updated_at', '>', 'last_published_at');
                });
            }

            return $query->get();
        });

        if ($tenants->isEmpty()) {
            $this->components->info('Nothing to publish.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($tenants as $tenant) {
            $name = $tenant->name ?? $tenant->getTenantKey();

            try {
                $publisher->publish($tenant);
                $this->components->twoColumnDetail($name, '<fg=green>published</>');
            } catch (\Throwable $exception) {
                $failed++;
                $this->components->twoColumnDetail($name, '<fg=red>failed</>');
                report($exception);
            }
        }

        // A partial failure must be visible to whatever ran this, or a stale
        // page sits there looking healthy.
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
