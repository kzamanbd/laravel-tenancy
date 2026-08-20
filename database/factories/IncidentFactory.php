<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'status' => IncidentStatus::Investigating,
            'impact' => IncidentImpact::Minor,
            'is_published' => true,
            'started_at' => now(),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => IncidentStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    /**
     * An incident still held back from the public page.
     */
    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    public function withImpact(IncidentImpact $impact): static
    {
        return $this->state(fn (): array => ['impact' => $impact]);
    }
}
