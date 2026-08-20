<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Enums\ComponentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreComponentRequest;
use App\Http\Requests\Workspace\UpdateComponentRequest;
use App\Models\Component;
use App\Models\ComponentGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Every query here reads without a `where tenant_id`: the connection is already
 * constrained by Row-Level Security for the tenant resolved from the path.
 */
class ComponentController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Component::class);

        return Inertia::render('workspace/components', [
            'components' => Component::query()
                ->with('group:id,name')
                ->orderBy('position')
                ->orderBy('name')
                ->get()
                ->map(fn (Component $component): array => [
                    'id' => $component->id,
                    'name' => $component->name,
                    'description' => $component->description,
                    'status' => $component->status->value,
                    'statusLabel' => $component->status->label(),
                    'isPublic' => $component->is_public,
                    'showUptime' => $component->show_uptime,
                    'group' => $component->group?->only(['id', 'name']),
                    'statusChangedAt' => $component->status_changed_at?->toIso8601String(),
                ]),
            'groups' => ComponentGroup::query()->orderBy('position')->get(['id', 'name']),
            'statuses' => $this->statusOptions(),
            'can' => [
                'create' => Gate::allows('create', Component::class),
            ],
        ]);
    }

    public function store(StoreComponentRequest $request): RedirectResponse
    {
        $component = new Component($request->validated());
        $component->position = (int) Component::query()->max('position') + 1;
        $component->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Component created.')]);

        return back();
    }

    public function update(UpdateComponentRequest $request, Component $component): RedirectResponse
    {
        $component->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Component updated.')]);

        return back();
    }

    public function destroy(Component $component): RedirectResponse
    {
        Gate::authorize('delete', $component);

        $component->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Component deleted.')]);

        return back();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (ComponentStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            ComponentStatus::cases(),
        );
    }
}
