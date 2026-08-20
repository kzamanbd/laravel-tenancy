<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentUpdate>
 */
class IncidentUpdateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'incident_id' => Incident::factory(),
            'author_id' => null,
            'status' => IncidentStatus::Investigating,
            'body' => fake()->paragraph(),
            'is_ai_drafted' => false,
            'published_at' => now(),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    /**
     * A draft sitting in the approval queue.
     */
    public function aiDraft(): static
    {
        return $this->state(fn (): array => [
            'is_ai_drafted' => true,
            'published_at' => null,
        ]);
    }
}
