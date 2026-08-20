<?php

declare(strict_types=1);

namespace App\Enums;

enum MembershipRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function canPublishIncidents(): bool
    {
        return $this !== self::Viewer;
    }

    public function canManageBilling(): bool
    {
        return $this === self::Owner;
    }

    public function canManageMembers(): bool
    {
        return in_array($this, [self::Owner, self::Admin], strict: true);
    }
}
