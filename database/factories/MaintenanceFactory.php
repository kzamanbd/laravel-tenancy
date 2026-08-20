<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MaintenanceStatus;
use App\Models\Maintenance;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        $start = now()->addDay();

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'description' => fake()->paragraph(),
            'status' => MaintenanceStatus::Scheduled,
            'is_published' => true,
            'auto_transition' => true,
            'notify_subscribers' => true,
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $start->addHours(2),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    /**
     * A window whose start time has already passed, so the scheduler should
     * transition it on the next pass.
     */
    public function due(): static
    {
        return $this->state(fn (): array => [
            'scheduled_start_at' => now()->subMinutes(5),
            'scheduled_end_at' => now()->addHour(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status' => MaintenanceStatus::InProgress,
            'started_at' => now()->subMinutes(10),
        ]);
    }
}
