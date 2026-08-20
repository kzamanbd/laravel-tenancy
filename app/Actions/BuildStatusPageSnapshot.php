<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ComponentStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Maintenance;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;

/**
 * Builds the complete public view of a status page as a plain array.
 *
 * This is the only place that reads the database for the public page. Once the
 * snapshot is written to object storage, serving the page involves no query, no
 * application code, and no dependency on this deployment being up -- which is
 * the entire point: a status page that dies with the service it reports on is
 * worse than no status page at all.
 *
 * Only published, public records appear here. Anything held back in the admin
 * must never leak into a file that gets served from a CDN.
 */
class BuildStatusPageSnapshot
{
    public function __construct(private TenantContext $context) {}

    /**
     * @return array<string, mixed>
     */
    public function __invoke(Tenant $tenant): array
    {
        // Publishing runs from queue workers and the console, where no tenant is
        // resolved. Bind the connection to this tenant for the duration of the
        // read rather than relying on ambient state.
        return $this->context->withoutIsolation(function () use ($tenant): array {
            $components = Component::query()
                ->where('tenant_id', $tenant->getTenantKey())
                ->where('is_public', true)
                ->orderBy('position')
                ->orderBy('name')
                ->get();

            $incidents = Incident::query()
                ->where('tenant_id', $tenant->getTenantKey())
                ->where('is_published', true)
                ->with(['updates', 'components:id,name'])
                ->orderByDesc('started_at')
                ->limit(50)
                ->get();

            $maintenances = Maintenance::query()
                ->where('tenant_id', $tenant->getTenantKey())
                ->where('is_published', true)
                ->whereIn('status', [MaintenanceStatus::Scheduled, MaintenanceStatus::InProgress])
                ->orderBy('scheduled_start_at')
                ->get();

            $overall = $this->overallStatus($components);

            return [
                'page' => [
                    'name' => $tenant->name ?? (string) $tenant->getTenantKey(),
                    'headline' => $tenant->headline,
                    'supportUrl' => $tenant->support_url,
                    'logoPath' => $tenant->logo_path,
                    'primaryColor' => $tenant->primary_color,
                    'customCss' => $this->sanitiseCss($tenant->custom_css),
                    'timezone' => $tenant->timezone,
                    'showPoweredBy' => $tenant->show_powered_by,
                    'subscribeUrl' => route('subscriptions.store', ['tenant' => $tenant->getTenantKey()]),
                ],
                'status' => [
                    'value' => $overall->value,
                    'label' => $overall->label(),
                    'description' => $this->statusDescription($overall),
                ],
                'components' => $components
                    ->map(fn (Component $component): array => [
                        'id' => $component->id,
                        'name' => $component->name,
                        'description' => $component->description,
                        'status' => $component->status->value,
                        'statusLabel' => $component->status->label(),
                    ])
                    ->values()
                    ->all(),
                'incidents' => $incidents
                    ->map(fn (Incident $incident): array => [
                        'id' => $incident->id,
                        'slug' => $incident->slug,
                        'title' => $incident->title,
                        'status' => $incident->status->value,
                        'statusLabel' => $incident->status->label(),
                        'impact' => $incident->impact->value,
                        'impactLabel' => $incident->impact->label(),
                        'startedAt' => $incident->started_at?->toIso8601String(),
                        'resolvedAt' => $incident->resolved_at?->toIso8601String(),
                        'components' => $incident->components->pluck('name')->values()->all(),
                        // Unpublished updates are the internal review queue and
                        // must never reach a file served from a CDN.
                        'updates' => $incident->updates
                            ->filter(fn (IncidentUpdate $update): bool => $update->isPublished())
                            ->sortByDesc('published_at')
                            ->map(fn (IncidentUpdate $update): array => [
                                'status' => $update->status->value,
                                'statusLabel' => $update->status->label(),
                                'body' => $update->body,
                                'publishedAt' => $update->published_at?->toIso8601String(),
                            ])
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all(),
                'maintenances' => $maintenances
                    ->map(fn (Maintenance $maintenance): array => [
                        'id' => $maintenance->id,
                        'slug' => $maintenance->slug,
                        'title' => $maintenance->title,
                        'description' => $maintenance->description,
                        'status' => $maintenance->status->value,
                        'statusLabel' => $maintenance->status->label(),
                        'scheduledStartAt' => $maintenance->scheduled_start_at->toIso8601String(),
                        'scheduledEndAt' => $maintenance->scheduled_end_at->toIso8601String(),
                    ])
                    ->values()
                    ->all(),
                'generatedAt' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * The page-wide banner: the worst component status present wins, because
     * "mostly operational" is not a thing customers believe.
     *
     * @param  Collection<int, Component>  $components
     */
    private function overallStatus(Collection $components): ComponentStatus
    {
        return $components
            ->sortByDesc(fn (Component $component): int => $component->status->severity())
            ->first()
            ?->status ?? ComponentStatus::Operational;
    }

    /**
     * Custom CSS is written verbatim into a <style> element on a page served
     * from a CDN, so it is attacker-controlled markup unless it is cleaned.
     *
     * `<` has no valid use in CSS, and removing it is what prevents closing the
     * style element and opening a script. `@import` is dropped separately: it
     * would fetch a third-party URL from the one page that must not depend on
     * anything external, and would leak every visitor to that host.
     */
    private function sanitiseCss(?string $css): ?string
    {
        if ($css === null || trim($css) === '') {
            return null;
        }

        $clean = str_replace('<', '', $css);
        $clean = preg_replace('/@import\b[^;]*;?/i', '', $clean) ?? '';

        return trim($clean) === '' ? null : $clean;
    }

    private function statusDescription(ComponentStatus $status): string
    {
        return match ($status) {
            ComponentStatus::Operational => 'All systems operational',
            ComponentStatus::UnderMaintenance => 'Maintenance in progress',
            ComponentStatus::DegradedPerformance => 'Degraded performance',
            ComponentStatus::PartialOutage => 'Partial system outage',
            ComponentStatus::MajorOutage => 'Major system outage',
        };
    }
}
