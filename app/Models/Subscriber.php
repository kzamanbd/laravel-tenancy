<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriberChannel;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Someone who has asked to hear about this page's incidents.
 *
 * @property int $id
 * @property int $tenant_id
 * @property SubscriberChannel $channel
 * @property string $endpoint
 * @property string|null $confirmation_token
 * @property string $unsubscribe_token
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $unsubscribed_at
 * @property array<int, int>|null $component_ids
 * @property int $bounce_count
 * @property Carbon|null $last_notified_at
 */
#[Fillable(['channel', 'endpoint', 'component_ids'])]
#[Hidden(['confirmation_token', 'unsubscribe_token'])]
class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => SubscriberChannel::class,
            'component_ids' => 'array',
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'last_notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscriber): void {
            $subscriber->unsubscribe_token ??= Str::random(48);

            if ($subscriber->channel->requiresDoubleOptIn()) {
                $subscriber->confirmation_token ??= Str::random(48);
            }
        });
    }

    /**
     * Whether anything may actually be delivered to this endpoint.
     *
     * Double opt-in is enforced here rather than at the send site, because one
     * abusive tenant degrades sending reputation for every other tenant.
     */
    public function isDeliverable(): bool
    {
        if ($this->unsubscribed_at !== null) {
            return false;
        }

        return ! $this->channel->requiresDoubleOptIn() || $this->confirmed_at !== null;
    }

    /**
     * Null `component_ids` means the subscriber follows the whole page.
     */
    public function followsComponent(int $componentId): bool
    {
        return $this->component_ids === null
            || in_array($componentId, $this->component_ids, strict: true);
    }
}
