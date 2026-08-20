<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MonitorType;
use App\Models\Monitor;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Monitor>
 */
class MonitorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'component_id' => null,
            'name' => fake()->unique()->domainWord().' check',
            'type' => MonitorType::Http,
            'target' => fake()->url(),
            'expected_status_code' => 200,
            'interval_seconds' => 60,
            'failure_threshold' => 3,
            'is_enabled' => true,
            'auto_open_incident' => false,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    public function autoOpening(): static
    {
        return $this->state(fn (): array => ['auto_open_incident' => true]);
    }

    /**
     * Enough consecutive failures to have tripped the threshold.
     */
    public function failing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'consecutive_failures' => $attributes['failure_threshold'] ?? 3,
            'last_status' => 'down',
            'last_checked_at' => now(),
        ]);
    }
}
