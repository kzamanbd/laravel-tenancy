<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MonitorType;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\MonitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A check against a component. The engine that runs these arrives in a later
 * phase; the table exists now so isolation covers it before it holds data.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $component_id
 * @property string $name
 * @property MonitorType $type
 * @property string|null $target
 * @property string|null $expected_keyword
 * @property int $expected_status_code
 * @property int $interval_seconds
 * @property int $failure_threshold
 * @property bool $is_enabled
 * @property bool $auto_open_incident
 * @property int $consecutive_failures
 * @property string|null $last_status
 * @property Carbon|null $last_checked_at
 */
#[Fillable([
    'component_id', 'name', 'type', 'target', 'expected_keyword',
    'expected_status_code', 'interval_seconds', 'failure_threshold',
    'is_enabled', 'auto_open_incident',
])]
class Monitor extends Model
{
    /** @use HasFactory<MonitorFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MonitorType::class,
            'is_enabled' => 'boolean',
            'auto_open_incident' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Component, $this>
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * Whether enough consecutive failures have accumulated to open an incident
     * without waiting for a human to notice.
     */
    public function shouldOpenIncident(): bool
    {
        return $this->auto_open_incident
            && $this->consecutive_failures >= $this->failure_threshold;
    }
}
