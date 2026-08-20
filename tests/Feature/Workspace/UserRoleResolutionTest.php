<?php

declare(strict_types=1);

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Role resolution
|--------------------------------------------------------------------------
|
| A membership is granted either on a single tenant or across an entire
| organization. User::roleFor() walks both, and a tenant-scoped grant must win
| so an owner can be demoted on one page without losing the organization.
|
*/

beforeEach(function () {
    [$this->organization, $this->tenant, $this->sibling, $this->outsideTenant] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);

        return [
            $organization,
            Tenant::factory()->forOrganization($organization)->create(),
            Tenant::factory()->forOrganization($organization)->create(),
            Tenant::factory()->create(),
        ];
    });
});

it('resolves a role granted on the tenant itself', function () {
    $user = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()->role(MembershipRole::Owner)->create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        return $user;
    });

    expect($user->roleFor($this->tenant))->toBe(MembershipRole::Owner)
        ->and($user->roleFor($this->sibling))->toBeNull();
});

it('resolves an organization-wide role on every tenant that organization owns', function () {
    $user = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()
            ->role(MembershipRole::Admin)
            ->organizationWide($this->organization->id)
            ->create(['user_id' => $user->id]);

        return $user;
    });

    expect($user->roleFor($this->tenant))->toBe(MembershipRole::Admin)
        ->and($user->roleFor($this->sibling))->toBe(MembershipRole::Admin)
        ->and($user->roleFor($this->outsideTenant))->toBeNull();
});

it('prefers a tenant-scoped role over an organization-wide one', function () {
    $user = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()
            ->role(MembershipRole::Owner)
            ->organizationWide($this->organization->id)
            ->create(['user_id' => $user->id]);

        Membership::factory()->role(MembershipRole::Viewer)->create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        return $user;
    });

    expect($user->roleFor($this->tenant))->toBe(MembershipRole::Viewer)
        ->and($user->roleFor($this->sibling))->toBe(MembershipRole::Owner);
});

it('grants no role to a user with no membership', function () {
    $stranger = asSuperAdmin(fn () => User::factory()->create());

    expect($stranger->roleFor($this->tenant))->toBeNull()
        ->and($stranger->canAccessTenant($this->tenant))->toBeFalse();
});
