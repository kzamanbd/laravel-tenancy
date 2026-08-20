<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\TriggersStatusPagePublish;
use Database\Factories\IncidentUpdateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One entry on an incident's timeline.
 *
 * An unpublished, AI-drafted update is the queue the operator approves during
 * an incident -- the difference between a page that stays honest and one that
 * sits green while support burns.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $incident_id
 * @property int|null $author_id
 * @property IncidentStatus $status
 * @property string $body
 * @property bool $is_ai_drafted
 * @property Carbon|null $published_at
 */
#[Fillable(['incident_id', 'author_id', 'status', 'body', 'is_ai_drafted', 'published_at'])]
class IncidentUpdate extends Model
{
    /** @use HasFactory<IncidentUpdateFactory> */
    use BelongsToTenant, HasFactory, TriggersStatusPagePublish;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => IncidentStatus::class,
            'is_ai_drafted' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Incident, $this>
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * A draft waiting on the single click that publishes it.
     */
    public function isAwaitingApproval(): bool
    {
        return $this->is_ai_drafted && ! $this->isPublished();
    }
}
