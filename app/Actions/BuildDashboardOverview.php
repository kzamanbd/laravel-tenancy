<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ComponentStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\Maintenance;
use App\Models\Subscriber;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;

/**
 * Assembles the numbers behind the dashboard.
 *
 * Two shapes, depending on where the request landed:
 *
 * - On a tenant domain, tenancy has already constrained the connection, so the
 *   queries here read naturally and Row-Level Security does the scoping.
 * - On the central domain no tenant is resolved, which means every
 *   tenant-scoped table reads as empty. Aggregating a user's whole portfolio is
 *   a genuinely cross-tenant operation, so it runs through the audited escape
 *   hatch with an explicit `whereIn` over the tenants that user can actually
 *   reach.
 */
class BuildDashboardOverview
{
    public function __construct(private TenantContext $context) {}

    /**
     * @return array{
     *     scope: 'tenant'|'portfolio',
     *     pages: list<array<string, mixed>>,
     *     totals: array<string, int>,
     *     statusBreakdown: list<array{label: string, value: int, status: string}>,
     *     incidents: list<array<string, mixed>>,
     *     maintenances: list<array<string, mixed>>,
     * }
     */
    public function forTenant(Tenant $tenant): array
    {
        $components = Component::query()->orderBy('position')->get();

        return [
            'scope' => 'tenant',
            'pages' => [$this->summarise($tenant, $components)],
            'totals' => [
                'pages' => 1,
                'components' => $components->count(),
                'openIncidents' => Incident::query()->whereNull('resolved_at')->count(),
                'subscribers' => Subscriber::query()
                    ->whereNotNull('confirmed_at')
                    ->whereNull('unsubscribed_at')
                    ->count(),
            ],
            'statusBreakdown' => $this->statusBreakdown($components),
            'incidents' => $this->recentIncidents(Incident::query()->with('updates')->get()),
            'maintenances' => $this->upcomingMaintenances(Maintenance::query()->get()),
        ];
    }

    /**
     * @return array{
     *     scope: 'tenant'|'portfolio',
     *     pages: list<array<string, mixed>>,
     *     totals: array<string, int>,
     *     statusBreakdown: list<array{label: string, value: int, status: string}>,
     *     incidents: list<array<string, mixed>>,
     *     maintenances: list<array<string, mixed>>,
     * }
     */
    public function forUser(User $user): array
    {
        // Memberships, not the `tenant_user` pivot: the portfolio has to list
        // exactly the pages the membership gate will let this user open.
        $tenants = $user->accessibleTenants()->get();
        $tenantIds = $tenants->pluck('id')->all();

        if ($tenantIds === []) {
            return [
                'scope' => 'portfolio',
                'pages' => [],
                'totals' => ['pages' => 0, 'components' => 0, 'openIncidents' => 0, 'subscribers' => 0],
                'statusBreakdown' => [],
                'incidents' => [],
                'maintenances' => [],
            ];
        }

        return $this->context->withoutIsolation(function () use ($tenants, $tenantIds): array {
            $components = Component::query()->whereIn('tenant_id', $tenantIds)->orderBy('position')->get();
            $incidents = Incident::query()->whereIn('tenant_id', $tenantIds)->with('updates')->get();
            $maintenances = Maintenance::query()->whereIn('tenant_id', $tenantIds)->get();

            $pages = $tenants
                ->map(fn (Tenant $tenant): array => $this->summarise(
                    $tenant,
                    $components->where('tenant_id', $tenant->id),
                    $incidents->where('tenant_id', $tenant->id)->whereNull('resolved_at')->count(),
                ))
                ->all();

            return [
                'scope' => 'portfolio',
                'pages' => $pages,
                'totals' => [
                    'pages' => $tenants->count(),
                    'components' => $components->count(),
                    'openIncidents' => $incidents->whereNull('resolved_at')->count(),
                    'subscribers' => Subscriber::query()
                        ->whereIn('tenant_id', $tenantIds)
                        ->whereNotNull('confirmed_at')
                        ->whereNull('unsubscribed_at')
                        ->count(),
                ],
                'statusBreakdown' => $this->statusBreakdown($components),
                'incidents' => $this->recentIncidents($incidents),
                'maintenances' => $this->upcomingMaintenances($maintenances),
            ];
        });
    }

    /**
     * A page is only "operational" when every one of its components is. The
     * worst component status becomes the page-wide banner.
     *
     * @param  Collection<int, Component>  $components
     * @return array<string, mixed>
     */
    private function summarise(Tenant $tenant, Collection $components, ?int $openIncidents = null): array
    {
        $worst = $components
            ->sortByDesc(fn (Component $component): int => $component->status->severity())
            ->first();

        return [
            'id' => $tenant->id,
            'name' => $tenant->name ?? (string) $tenant->id,
            'slug' => $tenant->slug,
            'status' => ($worst?->status ?? ComponentStatus::Operational)->value,
            'statusLabel' => ($worst?->status ?? ComponentStatus::Operational)->label(),
            'componentCount' => $components->count(),
            'degradedCount' => $components
                ->reject(fn (Component $component): bool => $component->isOperational())
                ->count(),
            'openIncidents' => $openIncidents ?? Incident::query()->whereNull('resolved_at')->count(),
        ];
    }

    /**
     * How many components sit at each status, worst first. Only statuses that
     * are actually present are returned, so the chart never renders empty
     * slices.
     *
     * @param  Collection<int, Component>  $components
     * @return list<array{label: string, value: int, status: string}>
     */
    private function statusBreakdown(Collection $components): array
    {
        return collect(ComponentStatus::cases())
            ->sortByDesc(fn (ComponentStatus $status): int => $status->severity())
            ->map(fn (ComponentStatus $status): array => [
                'status' => $status->value,
                'label' => $status->label(),
                'value' => $components
                    ->filter(fn (Component $component): bool => $component->status === $status)
                    ->count(),
            ])
            ->filter(fn (array $slice): bool => $slice['value'] > 0)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Incident>  $incidents
     * @return list<array<string, mixed>>
     */
    private function recentIncidents(Collection $incidents): array
    {
        return $incidents
            ->sortByDesc(fn (Incident $incident) => $incident->started_at ?? $incident->created_at)
            ->take(5)
            ->map(fn (Incident $incident): array => [
                'id' => $incident->id,
                'title' => $incident->title,
                'status' => $incident->status->value,
                'statusLabel' => $incident->status->label(),
                'impact' => $incident->impact->value,
                'impactLabel' => $incident->impact->label(),
                'startedAt' => $incident->started_at?->toIso8601String(),
                'resolvedAt' => $incident->resolved_at?->toIso8601String(),
                'updateCount' => $incident->updates->count(),
                'awaitingApproval' => $incident->updates
                    ->contains(fn ($update): bool => $update->isAwaitingApproval()),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Maintenance>  $maintenances
     * @return list<array<string, mixed>>
     */
    private function upcomingMaintenances(Collection $maintenances): array
    {
        return $maintenances
            ->filter(fn (Maintenance $maintenance): bool => in_array(
                $maintenance->status,
                [MaintenanceStatus::Scheduled, MaintenanceStatus::InProgress],
                strict: true,
            ))
            ->sortBy('scheduled_start_at')
            ->take(5)
            ->map(fn (Maintenance $maintenance): array => [
                'id' => $maintenance->id,
                'title' => $maintenance->title,
                'status' => $maintenance->status->value,
                'statusLabel' => $maintenance->status->label(),
                'scheduledStartAt' => $maintenance->scheduled_start_at->toIso8601String(),
                'scheduledEndAt' => $maintenance->scheduled_end_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
