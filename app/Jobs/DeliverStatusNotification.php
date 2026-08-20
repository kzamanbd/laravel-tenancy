<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SubscriberChannel;
use App\Mail\StatusUpdateMail;
use App\Models\Subscriber;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers one update to one subscriber.
 *
 * One job per subscriber rather than per batch, so a single dead webhook or
 * refused mailbox retries on its own instead of taking a whole batch with it.
 *
 * The rate limiter is keyed by tenant, which is the point: an incident at a
 * page with 40,000 subscribers would otherwise occupy every worker and delay
 * delivery for every other tenant -- and the tenants being delayed are having
 * their own incident, because that is when notifications happen. Over the
 * limit, the job is released back to the queue rather than dropped.
 */
class DeliverStatusNotification implements ShouldQueue
{
    use Queueable;

    /** Long enough for a slow webhook, short enough not to hold a worker. */
    public int $timeout = 30;

    public int $tries = 3;

    /** After this many hard failures the endpoint is treated as dead. */
    private const BOUNCE_LIMIT = 5;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $subscriberId,
        public int $tenantId,
        public array $payload,
    ) {}

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [new RateLimited('status-notifications')];
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(TenantContext $context): void
    {
        [$subscriber, $tenant] = $context->withoutIsolation(fn (): array => [
            Subscriber::query()->find($this->subscriberId),
            Tenant::query()->find($this->tenantId),
        ]);

        if ($subscriber === null || $tenant === null) {
            return;
        }

        // Re-checked at the moment of sending, not just at fan-out: someone who
        // unsubscribed while a large fan-out was still draining must not
        // receive the tail of it.
        if (! $subscriber->isDeliverable()) {
            return;
        }

        try {
            $this->deliver($subscriber, $tenant);
        } catch (\Throwable $exception) {
            $this->recordFailure($context, $subscriber, $exception);

            throw $exception;
        }

        $context->withoutIsolation(function () use ($subscriber): void {
            $subscriber->forceFill([
                'last_notified_at' => now(),
                'bounce_count' => 0,
            ])->save();
        });
    }

    private function deliver(Subscriber $subscriber, Tenant $tenant): void
    {
        match ($subscriber->channel) {
            SubscriberChannel::Email => Mail::to($subscriber->endpoint)
                ->send(new StatusUpdateMail($tenant, $subscriber, $this->payload)),
            SubscriberChannel::Slack => $this->postJson($subscriber->endpoint, $this->slackBody($tenant)),
            SubscriberChannel::Discord => $this->postJson($subscriber->endpoint, [
                'content' => $this->plainText($tenant),
            ]),
            SubscriberChannel::Teams => $this->postJson($subscriber->endpoint, [
                'text' => $this->plainText($tenant),
            ]),
            SubscriberChannel::Webhook => $this->postJson($subscriber->endpoint, [
                'page' => $tenant->name,
                'event' => $this->payload,
            ]),
            // SMS is metered and arrives with the billing work; delivering it
            // silently for free would invert the margin on the cheapest plan.
            SubscriberChannel::Sms => null,
        };
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function postJson(string $url, array $body): void
    {
        Http::asJson()
            ->timeout(10)
            ->withUserAgent(config('app.name').' status notifier')
            ->post($url, $body)
            ->throw();
    }

    /**
     * @return array<string, mixed>
     */
    private function slackBody(Tenant $tenant): array
    {
        return [
            'text' => $this->plainText($tenant),
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*{$this->payload['heading']}* — {$this->payload['status']}\n{$this->payload['body']}",
                    ],
                ],
            ],
        ];
    }

    private function plainText(Tenant $tenant): string
    {
        $affected = $this->payload['componentNames'] === []
            ? ''
            : ' ('.implode(', ', $this->payload['componentNames']).')';

        return "[{$tenant->name}] {$this->payload['heading']} — {$this->payload['status']}{$affected}\n"
            .$this->payload['body'];
    }

    /**
     * A repeatedly failing endpoint is unsubscribed rather than retried
     * forever. Continuing to send at a dead mailbox is what turns a shared
     * sending reputation into a blocked one, and every other tenant pays for
     * it.
     */
    private function recordFailure(TenantContext $context, Subscriber $subscriber, \Throwable $exception): void
    {
        $context->withoutIsolation(function () use ($subscriber): void {
            $subscriber->increment('bounce_count');

            if ($subscriber->fresh()->bounce_count >= self::BOUNCE_LIMIT) {
                $subscriber->forceFill(['unsubscribed_at' => now()])->save();
            }
        });

        Log::warning('Status notification delivery failed', [
            'subscriber_id' => $subscriber->id,
            'tenant_id' => $this->tenantId,
            'channel' => $subscriber->channel->value,
            'error' => $exception->getMessage(),
        ]);
    }
}
