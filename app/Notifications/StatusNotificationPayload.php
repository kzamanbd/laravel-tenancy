<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Maintenance;

/**
 * What subscribers are being told, independent of how it reaches them.
 *
 * Built once at fan-out and carried through every delivery, so email, Slack and
 * webhook cannot drift into saying different things about the same event -- and
 * so a subscriber is not re-reading the database once per channel.
 */
class StatusNotificationPayload
{
    /**
     * @param  list<string>  $componentNames
     * @param  list<int>  $componentIds
     */
    public function __construct(
        public string $heading,
        public string $body,
        public ?string $impact,
        public string $status,
        public array $componentNames = [],
        public array $componentIds = [],
        public ?int $incidentId = null,
        public ?int $maintenanceId = null,
    ) {}

    public static function forIncidentUpdate(IncidentUpdate $update, Incident $incident): self
    {
        return new self(
            heading: $incident->title,
            body: $update->body,
            impact: $incident->impact->label(),
            status: $update->status->label(),
            componentNames: $incident->components->pluck('name')->values()->all(),
            componentIds: $incident->components->pluck('id')->values()->all(),
            incidentId: $incident->id,
        );
    }

    public static function forMaintenance(Maintenance $maintenance, string $body): self
    {
        return new self(
            heading: $maintenance->title,
            body: $body,
            impact: null,
            status: $maintenance->status->label(),
            componentNames: $maintenance->components->pluck('name')->values()->all(),
            componentIds: $maintenance->components->pluck('id')->values()->all(),
            maintenanceId: $maintenance->id,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'body' => $this->body,
            'impact' => $this->impact,
            'status' => $this->status,
            'componentNames' => $this->componentNames,
            'componentIds' => $this->componentIds,
            'incidentId' => $this->incidentId,
            'maintenanceId' => $this->maintenanceId,
        ];
    }
}
