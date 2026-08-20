<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ComponentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A single piece of the service whose health is reported on the page.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $component_group_id
 * @property string $name
 * @property string|null $description
 * @property ComponentStatus $status
 * @property int $position
 * @property bool $is_public
 * @property bool $show_uptime
 * @property Carbon|null $status_changed_at
 */
#[Fillable(['name', 'description', 'status', 'position', 'is_public', 'show_uptime', 'component_group_id'])]
class Component extends Model
{
    /** @use HasFactory<ComponentFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ComponentStatus::class,
            'is_public' => 'boolean',
            'show_uptime' => 'boolean',
            'status_changed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $component): void {
            if ($component->isDirty('status')) {
                $component->status_changed_at = now();
            }
        });
    }

    /**
     * @return BelongsTo<ComponentGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ComponentGroup::class, 'component_group_id');
    }

    /**
     * @return BelongsToMany<Incident, $this>
     */
    public function incidents(): BelongsToMany
    {
        return $this->belongsToMany(Incident::class, 'component_incident')
            ->withPivot(['tenant_id', 'status']);
    }

    /**
     * @return BelongsToMany<Maintenance, $this>
     */
    public function maintenances(): BelongsToMany
    {
        return $this->belongsToMany(Maintenance::class, 'component_maintenance')
            ->withPivot('tenant_id');
    }

    /**
     * @return HasMany<Monitor, $this>
     */
    public function monitors(): HasMany
    {
        return $this->hasMany(Monitor::class);
    }

    public function isOperational(): bool
    {
        return $this->status->isOperational();
    }
}
