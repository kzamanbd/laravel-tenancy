<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenanceStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property MaintenanceStatus $status
 * @property bool $is_published
 * @property bool $auto_transition
 * @property bool $notify_subscribers
 * @property Carbon $scheduled_start_at
 * @property Carbon $scheduled_end_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 */
#[Fillable([
    'title', 'slug', 'description', 'status', 'is_published',
    'auto_transition', 'notify_subscribers',
    'scheduled_start_at', 'scheduled_end_at',
])]
class Maintenance extends Model
{
    /** @use HasFactory<MaintenanceFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MaintenanceStatus::class,
            'is_published' => 'boolean',
            'auto_transition' => 'boolean',
            'notify_subscribers' => 'boolean',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Component, $this>
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'component_maintenance')
            ->withPivot('tenant_id');
    }

    /**
     * Whether the scheduled window has opened and the status should move to
     * in-progress on the next scheduler pass.
     */
    public function isDueToStart(): bool
    {
        return $this->status === MaintenanceStatus::Scheduled
            && $this->auto_transition
            && $this->scheduled_start_at->isPast();
    }

    public function isDueToComplete(): bool
    {
        return $this->status === MaintenanceStatus::InProgress
            && $this->auto_transition
            && $this->scheduled_end_at->isPast();
    }
}
