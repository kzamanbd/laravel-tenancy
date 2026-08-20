<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Plan;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(5),
            'plan' => Plan::Free,
            'is_agency' => false,
            'tenant_quota' => 1,
            'billing_email' => fake()->safeEmail(),
        ];
    }

    public function onPlan(Plan $plan): static
    {
        return $this->state(fn (): array => [
            'plan' => $plan,
            'tenant_quota' => $plan === Plan::Agency ? 10 : 1,
        ]);
    }

    /**
     * An agency reselling pages to its own clients.
     */
    public function agency(): static
    {
        return $this->state(fn (): array => [
            'is_agency' => true,
            'plan' => Plan::Agency,
            'tenant_quota' => 10,
        ]);
    }
}
