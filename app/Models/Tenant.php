<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * A tenant is one workspace, which is exactly one status page.
 *
 * Tenancy is single-database: rows for every tenant share the same tables and
 * are separated by PostgreSQL Row-Level Security rather than by connection.
 * `DatabaseTenancyBootstrapper` is therefore intentionally left disabled in
 * `config/tenancy.php`.
 *
 * @property int $id
 * @property int|null $organization_id
 * @property string|null $name
 * @property string|null $slug
 * @property Carbon|null $published_at
 * @property string|null $headline
 * @property string|null $support_url
 * @property string|null $logo_path
 * @property string $primary_color
 * @property string|null $custom_css
 * @property string $timezone
 * @property bool $show_powered_by
 * @property Carbon|null $last_published_at
 */
class Tenant extends BaseTenant
{
    /** @use HasFactory<TenantFactory> */
    use HasDomains, HasFactory;

    /**
     * Columns promoted out of stancl's `data` JSON blob into real columns, so
     * they can be indexed, constrained, and joined against.
     *
     * @return array<int, string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'organization_id',
            'name',
            'slug',
            'published_at',
            'headline',
            'support_url',
            'logo_path',
            'primary_color',
            'custom_css',
            'timezone',
            'show_powered_by',
            'last_published_at',
        ];
    }

    /**
     * Mirrors the column defaults so a freshly created tenant carries them in
     * memory. A database default is not applied until after the insert, and the
     * publisher reads these straight off the model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'primary_color' => '#4f46e5',
        'timezone' => 'UTC',
        'show_powered_by' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'last_published_at' => 'datetime',
            'show_powered_by' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * @return HasMany<ComponentGroup, $this>
     */
    public function componentGroups(): HasMany
    {
        return $this->hasMany(ComponentGroup::class);
    }

    /**
     * @return HasMany<Component, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    /**
     * @return HasMany<Incident, $this>
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * @return HasMany<Maintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * @return HasMany<Subscriber, $this>
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class);
    }

    /**
     * @return HasMany<Monitor, $this>
     */
    public function monitors(): HasMany
    {
        return $this->hasMany(Monitor::class);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
