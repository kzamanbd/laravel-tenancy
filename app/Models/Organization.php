<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plan;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * The billing entity, sitting one level above the tenant.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Plan $plan
 * @property bool $is_agency
 * @property int|null $parent_organization_id
 * @property int $tenant_quota
 * @property string|null $billing_email
 * @property string|null $stripe_customer_id
 * @property Carbon|null $trial_ends_at
 */
#[Fillable(['name', 'slug', 'plan', 'is_agency', 'parent_organization_id', 'tenant_quota', 'billing_email'])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use CentralConnection, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan' => Plan::class,
            'is_agency' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Tenant, $this>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * The reseller that owns this organization, when it was provisioned by an
     * agency rather than signing up directly.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function parentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_organization_id');
    }

    /**
     * @return HasMany<Organization, $this>
     */
    public function subOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_organization_id');
    }

    public function hasReachedTenantQuota(): bool
    {
        return $this->tenants()->count() >= $this->tenant_quota;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }
}
