<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Component>
 */
class ComponentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->randomElement([
                'REST API', 'Web App', 'Webhooks', 'Background Jobs',
                'Search', 'File Storage', 'Authentication', 'CDN',
            ]).' '.fake()->unique()->randomNumber(4),
            'description' => fake()->sentence(),
            'status' => ComponentStatus::Operational,
            'position' => 0,
            'is_public' => true,
            'show_uptime' => true,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    public function withStatus(ComponentStatus $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_public' => false]);
    }
}
