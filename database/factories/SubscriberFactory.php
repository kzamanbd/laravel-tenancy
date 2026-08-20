<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriberChannel;
use App\Models\Subscriber;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'channel' => SubscriberChannel::Email,
            'endpoint' => fake()->unique()->safeEmail(),
            'confirmed_at' => now(),
            'component_ids' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    /**
     * Signed up but has not clicked the confirmation link, so nothing may be
     * delivered to them yet.
     */
    public function unconfirmed(): static
    {
        return $this->state(fn (): array => ['confirmed_at' => null]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (): array => ['unsubscribed_at' => now()]);
    }

    public function onChannel(SubscriberChannel $channel): static
    {
        return $this->state(fn (): array => [
            'channel' => $channel,
            'endpoint' => $channel === SubscriberChannel::Email
                ? fake()->unique()->safeEmail()
                : fake()->unique()->url(),
        ]);
    }
}
