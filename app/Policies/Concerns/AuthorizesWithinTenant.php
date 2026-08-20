<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Enums\MembershipRole;
use App\Models\Tenant;
use App\Models\User;

/**
 * Shared role resolution for the tenant-scoped policies.
 *
 * Every one of these policies is reached only from a route where tenancy has
 * already been initialized and `EnsureUserBelongsToTenant` has run, so the
 * question is never "which tenant?" but "what may this user do here?".
 */
trait AuthorizesWithinTenant
{
    protected function role(User $user): ?MembershipRole
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return null;
        }

        return $user->roleFor($tenant);
    }

    /**
     * Anyone with a membership can read; the public page shows most of this
     * anyway once published.
     */
    protected function canRead(User $user): bool
    {
        return $this->role($user) !== null;
    }

    /**
     * Viewers are deliberately read-only. Editors and above may change what
     * the page reports.
     */
    protected function canWrite(User $user): bool
    {
        return $this->role($user)?->canPublishIncidents() ?? false;
    }

    /**
     * Destructive changes are limited to admins and owners: deleting a
     * component silently rewrites published incident history.
     */
    protected function canAdminister(User $user): bool
    {
        return $this->role($user)?->canManageMembers() ?? false;
    }
}
