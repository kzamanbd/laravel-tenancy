<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Subscriber;
use App\Models\Tenant;
use App\Notifications\StatusNotificationPayload;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Turns one published update into one delivery job per subscriber.
 *
 * Split in two on purpose. Fanning out is a single cheap query; delivering is
 * thousands of slow network calls, and the two must not share a failure mode.
 * If one address times out, the other 39,999 should still go.
 *
 * Subscribers are chunked rather than loaded at once: a page with 40k
 * confirmed addresses would otherwise hydrate 40k models inside one worker.
 */
class FanOutStatusNotification implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $tenantId,
        public array $payload,
    ) {}

    public static function for(Tenant $tenant, StatusNotificationPayload $payload): self
    {
        return new self($tenant->getTenantKey(), $payload->toArray());
    }

    public function handle(TenantContext $context): void
    {
        $componentIds = $this->payload['componentIds'] ?? [];

        $context->withoutIsolation(function () use ($componentIds): void {
            Subscriber::query()
                ->where('tenant_id', $this->tenantId)
                ->whereNull('unsubscribed_at')
                ->orderBy('id')
                ->chunkById(500, function ($subscribers) use ($componentIds): void {
                    foreach ($subscribers as $subscriber) {
                        if (! $subscriber->isDeliverable()) {
                            continue;
                        }

                        if (! $this->followsAnyAffectedComponent($subscriber, $componentIds)) {
                            continue;
                        }

                        DeliverStatusNotification::dispatch(
                            $subscriber->id,
                            $this->tenantId,
                            $this->payload,
                        );
                    }
                });
        });
    }

    /**
     * A subscriber who chose specific components should not be paged about an
     * incident that does not touch any of them. Page-wide events (no components
     * named) still reach everyone.
     *
     * @param  list<int>  $componentIds
     */
    private function followsAnyAffectedComponent(Subscriber $subscriber, array $componentIds): bool
    {
        if ($componentIds === [] || $subscriber->component_ids === null) {
            return true;
        }

        foreach ($componentIds as $componentId) {
            if ($subscriber->followsComponent((int) $componentId)) {
                return true;
            }
        }

        return false;
    }
}
