<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWithinTenant;

/**
 * Row-Level Security already guarantees a user can only ever be handed an
 * Incident belonging to the resolved tenant, so these checks are purely about
 * role, never ownership.
 */
class IncidentPolicy
{
    use AuthorizesWithinTenant;

    public function viewAny(User $user): bool
    {
        return $this->canRead($user);
    }

    public function view(User $user, Incident $incident): bool
    {
        return $this->canRead($user);
    }

    public function create(User $user): bool
    {
        return $this->canWrite($user);
    }

    public function update(User $user, Incident $incident): bool
    {
        return $this->canWrite($user);
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $this->canAdminister($user);
    }

    /**
     * Posting an update is the same authority as opening the incident: this is
     * the action the product is built around, and gating it more tightly than
     * `update` would mean a page sits stale while someone hunts for an admin.
     */
    public function comment(User $user, Incident $incident): bool
    {
        return $this->canWrite($user);
    }
}
