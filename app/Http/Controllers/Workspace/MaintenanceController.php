<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreMaintenanceRequest;
use App\Http\Requests\Workspace\UpdateMaintenanceRequest;
use App\Models\Component;
use App\Models\Maintenance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Maintenance::class);

        return Inertia::render('workspace/maintenance', [
            'maintenances' => Maintenance::query()
                ->with('components:id,name')
                ->orderByDesc('scheduled_start_at')
                ->get()
                ->map(fn (Maintenance $maintenance): array => [
                    'id' => $maintenance->id,
                    'title' => $maintenance->title,
                    'description' => $maintenance->description,
                    'status' => $maintenance->status->value,
                    'statusLabel' => $maintenance->status->label(),
                    'isPublished' => $maintenance->is_published,
                    'autoTransition' => $maintenance->auto_transition,
                    'notifySubscribers' => $maintenance->notify_subscribers,
                    'scheduledStartAt' => $maintenance->scheduled_start_at->toIso8601String(),
                    'scheduledEndAt' => $maintenance->scheduled_end_at->toIso8601String(),
                    'components' => $maintenance->components->map->only(['id', 'name'])->values(),
                ]),
            'components' => Component::query()->orderBy('position')->get(['id', 'name']),
            'statuses' => array_map(
                fn (MaintenanceStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ],
                MaintenanceStatus::cases(),
            ),
            'can' => [
                'create' => Gate::allows('create', Maintenance::class),
            ],
        ]);
    }

    public function store(StoreMaintenanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $maintenance = Maintenance::create([
                ...$validated,
                'slug' => $this->uniqueSlug($validated['title']),
                'status' => MaintenanceStatus::Scheduled,
            ]);

            $this->syncComponents($maintenance, $validated['component_ids'] ?? []);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Maintenance scheduled.')]);

        return back();
    }

    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance): RedirectResponse
    {
        $validated = $request->validated();
        $status = MaintenanceStatus::from($validated['status']);

        $maintenance->fill($validated);
        $maintenance->started_at = match (true) {
            $status === MaintenanceStatus::Scheduled => null,
            default => $maintenance->started_at ?? now(),
        };
        $maintenance->completed_at = $status === MaintenanceStatus::Completed
            ? ($maintenance->completed_at ?? now())
            : null;
        $maintenance->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Maintenance updated.')]);

        return back();
    }

    public function destroy(Maintenance $maintenance): RedirectResponse
    {
        Gate::authorize('delete', $maintenance);

        $maintenance->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Maintenance deleted.')]);

        return back();
    }

    /**
     * @param  list<int|string>  $componentIds
     */
    private function syncComponents(Maintenance $maintenance, array $componentIds): void
    {
        if ($componentIds === []) {
            return;
        }

        $maintenance->components()->sync(
            collect($componentIds)
                ->mapWithKeys(fn (int|string $id): array => [
                    (int) $id => ['tenant_id' => $maintenance->tenant_id],
                ])
                ->all(),
        );
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'maintenance';
        $slug = $base;
        $suffix = 1;

        while (Maintenance::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }
}
