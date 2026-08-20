<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => null,
            'tenant_id' => Tenant::factory(),
            'role' => MembershipRole::Viewer,
        ];
    }

    public function role(MembershipRole $role): static
    {
        return $this->state(fn (): array => ['role' => $role]);
    }

    /**
     * A membership that spans every tenant the organization owns.
     */
    public function organizationWide(int $organizationId): static
    {
        return $this->state(fn (): array => [
            'organization_id' => $organizationId,
            'tenant_id' => null,
        ]);
    }
}
