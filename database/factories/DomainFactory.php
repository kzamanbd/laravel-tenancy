<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DomainVerificationStatus;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'domain' => fake()->unique()->domainWord().'.'.config('tenancy.central_domains')[0],
            'is_primary' => true,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenant->id]);
    }

    /**
     * A customer-owned hostname, which must be verified before a certificate
     * is issued for it.
     */
    public function custom(): static
    {
        return $this->state(fn (): array => [
            'domain' => fake()->unique()->domainName(),
            'verification_status' => DomainVerificationStatus::Pending,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'verification_status' => DomainVerificationStatus::Verified,
            'verified_at' => now(),
        ]);
    }
}
