<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscriber;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWithinTenant;

class SubscriberPolicy
{
    use AuthorizesWithinTenant;

    public function viewAny(User $user): bool
    {
        return $this->canRead($user);
    }

    public function view(User $user, Subscriber $subscriber): bool
    {
        return $this->canRead($user);
    }

    public function create(User $user): bool
    {
        return $this->canWrite($user);
    }

    /**
     * Removing someone else's subscription on their behalf is an administrative
     * act, not an editorial one.
     */
    public function delete(User $user, Subscriber $subscriber): bool
    {
        return $this->canAdminister($user);
    }
}
