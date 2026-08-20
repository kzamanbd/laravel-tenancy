<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MembershipRole;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Grants a user a role, either across an entire organization or on a single
 * tenant. An organization-level membership implies the role on every tenant
 * that organization owns.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $organization_id
 * @property int|null $tenant_id
 * @property MembershipRole $role
 */
#[Fillable(['user_id', 'organization_id', 'tenant_id', 'role'])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use CentralConnection, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isOrganizationWide(): bool
    {
        return $this->tenant_id === null;
    }
}
