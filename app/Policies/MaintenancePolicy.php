<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWithinTenant;

/**
 * Row-Level Security already guarantees a user can only ever be handed a
 * Maintenance belonging to the resolved tenant, so these checks are purely about
 * role, never ownership.
 */
class MaintenancePolicy
{
    use AuthorizesWithinTenant;

    public function viewAny(User $user): bool
    {
        return $this->canRead($user);
    }

    public function view(User $user, Maintenance $maintenance): bool
    {
        return $this->canRead($user);
    }

    public function create(User $user): bool
    {
        return $this->canWrite($user);
    }

    public function update(User $user, Maintenance $maintenance): bool
    {
        return $this->canWrite($user);
    }

    /**
     * Deleting is limited to admins and owners: removing a component silently
     * rewrites already-published incident history.
     */
    public function delete(User $user, Maintenance $maintenance): bool
    {
        return $this->canAdminister($user);
    }
}
