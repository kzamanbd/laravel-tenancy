<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(5),
            'published_at' => now(),
        ];
    }

    /**
     * Give every tenant the platform subdomain it would be provisioned with.
     *
     * A tenant without a domain is not reachable: the workspace resolves the
     * tenant from the host, and the public page is served on it too. Leaving
     * factories domainless would make tests describe a state the application
     * never creates.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Tenant $tenant): void {
            if ($tenant->domains()->exists()) {
                return;
            }

            $tenant->createDomain($tenant->slug.'.'.config('tenancy.central_domains')[0]);
        });
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['published_at' => null]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (): array => ['organization_id' => $organization->id]);
    }
}
