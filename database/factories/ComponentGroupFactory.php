<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ComponentGroup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponentGroup>
 */
class ComponentGroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Core Platform', 'APIs', 'Dashboards', 'Integrations']),
            'position' => 0,
            'is_collapsed' => false,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }
}
