<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\MembershipRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Membership> $memberships
 * @property-read Collection<int, Tenant> $tenants
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use CentralConnection, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * The tenants this user belongs to.
     *
     * @return BelongsToMany<Tenant, $this>
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * The role this user holds on a tenant, or null if they hold none.
     *
     * A membership scoped to the tenant itself wins over one granted across
     * the whole organization, so an owner can be demoted on a single page
     * without losing their organization-wide role elsewhere.
     */
    public function roleFor(Tenant $tenant): ?MembershipRole
    {
        $this->loadMissing('memberships');

        $direct = $this->memberships
            ->firstWhere('tenant_id', $tenant->getTenantKey());

        if ($direct !== null) {
            return $direct->role;
        }

        return $this->memberships
            ->first(fn (Membership $membership): bool => $membership->isOrganizationWide()
                && $membership->organization_id === $tenant->organization_id
                && $tenant->organization_id !== null)
            ?->role;
    }

    /**
     * Whether this user may reach a tenant at all.
     *
     * Tenancy resolved from a URL path will happily initialize any tenant id a
     * visitor types, and Row-Level Security then scopes the request to exactly
     * that tenant. This check is what stops someone walking another
     * organization's pages by editing the address bar.
     */
    public function canAccessTenant(Tenant $tenant): bool
    {
        return $this->roleFor($tenant) !== null;
    }
}
