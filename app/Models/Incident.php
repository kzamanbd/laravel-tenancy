<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\TriggersStatusPagePublish;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $title
 * @property string $slug
 * @property IncidentStatus $status
 * @property IncidentImpact $impact
 * @property bool $is_published
 * @property int|null $opened_by_monitor_id
 * @property bool $notifications_sent
 * @property Carbon|null $started_at
 * @property Carbon|null $resolved_at
 */
#[Fillable(['title', 'slug', 'status', 'impact', 'is_published', 'started_at', 'resolved_at'])]
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use BelongsToTenant, HasFactory, TriggersStatusPagePublish;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => IncidentStatus::class,
            'impact' => IncidentImpact::class,
            'is_published' => 'boolean',
            'notifications_sent' => 'boolean',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<IncidentUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class)->latest('created_at');
    }

    /**
     * @return BelongsToMany<Component, $this>
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'component_incident')
            ->withPivot(['tenant_id', 'status']);
    }

    /**
     * Whether this incident was opened by the monitoring engine rather than a
     * person, which is what keeps a page honest when nobody has the time.
     */
    public function wasOpenedAutomatically(): bool
    {
        return $this->opened_by_monitor_id !== null;
    }

    public function isResolved(): bool
    {
        return $this->status->isTerminal();
    }
}
