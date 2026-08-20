<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\StatusPagePublisher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Republishes a status page after something it reports on changed.
 *
 * Unique per tenant: an incident update typically touches several records at
 * once, and every one of them asks for a republish. Collapsing those into a
 * single pending job keeps a busy incident from queuing dozens of identical
 * renders -- which matters most precisely when the system is already unhappy.
 */
class PublishStatusPage implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** Long enough to survive a slow object-storage write, short enough to retry. */
    public int $timeout = 60;

    public int $tries = 3;

    /**
     * Stop collapsing once the job has started, so a change made mid-render
     * still triggers a fresh publish rather than being swallowed.
     */
    public int $uniqueFor = 120;

    public function __construct(public Tenant $tenant) {}

    public function uniqueId(): string
    {
        return (string) $this->tenant->getTenantKey();
    }

    public function handle(StatusPagePublisher $publisher): void
    {
        $publisher->publish($this->tenant);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [5, 30];
    }
}
