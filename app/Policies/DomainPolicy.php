<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWithinTenant;

/**
 * Unlike the page's own records, `domains` is a central table that Row-Level
 * Security does not scope. Ownership is therefore checked explicitly in the
 * controller; this policy only answers what the user's role permits.
 */
class DomainPolicy
{
    use AuthorizesWithinTenant;

    public function viewAny(User $user): bool
    {
        return $this->canRead($user);
    }

    public function view(User $user, Domain $domain): bool
    {
        return $this->canRead($user);
    }

    public function create(User $user): bool
    {
        return $this->canWrite($user);
    }

    public function update(User $user, Domain $domain): bool
    {
        return $this->canWrite($user);
    }

    /**
     * Removing a hostname takes a customer's status page offline at the address
     * their own documentation points at, so it needs more than edit rights.
     */
    public function delete(User $user, Domain $domain): bool
    {
        return $this->canAdminister($user);
    }
}
