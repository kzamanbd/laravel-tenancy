<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Enums\ComponentStatus;
use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreIncidentRequest;
use App\Http\Requests\Workspace\UpdateIncidentRequest;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Incident::class);

        return Inertia::render('workspace/incidents/index', [
            'incidents' => Incident::query()
                ->withCount('updates')
                ->orderByDesc('started_at')
                ->get()
                ->map(fn (Incident $incident): array => [
                    'id' => $incident->id,
                    'title' => $incident->title,
                    'slug' => $incident->slug,
                    'status' => $incident->status->value,
                    'statusLabel' => $incident->status->label(),
                    'impact' => $incident->impact->value,
                    'impactLabel' => $incident->impact->label(),
                    'isPublished' => $incident->is_published,
                    'startedAt' => $incident->started_at?->toIso8601String(),
                    'resolvedAt' => $incident->resolved_at?->toIso8601String(),
                    'updateCount' => $incident->updates_count,
                ]),
            'components' => Component::query()->orderBy('position')->get(['id', 'name']),
            'statuses' => $this->options(IncidentStatus::cases()),
            'impacts' => $this->options(IncidentImpact::cases()),
            'componentStatuses' => $this->options(ComponentStatus::cases()),
            'can' => [
                'create' => Gate::allows('create', Incident::class),
            ],
        ]);
    }

    public function show(Incident $incident): Response
    {
        Gate::authorize('view', $incident);

        $incident->load(['updates.author:id,name', 'components:id,name']);

        return Inertia::render('workspace/incidents/show', [
            'incident' => [
                'id' => $incident->id,
                'title' => $incident->title,
                'status' => $incident->status->value,
                'statusLabel' => $incident->status->label(),
                'impact' => $incident->impact->value,
                'impactLabel' => $incident->impact->label(),
                'isPublished' => $incident->is_published,
                'startedAt' => $incident->started_at?->toIso8601String(),
                'resolvedAt' => $incident->resolved_at?->toIso8601String(),
                'components' => $incident->components->map->only(['id', 'name'])->values(),
                'updates' => $incident->updates->map(fn (IncidentUpdate $update): array => [
                    'id' => $update->id,
                    'status' => $update->status->value,
                    'statusLabel' => $update->status->label(),
                    'body' => $update->body,
                    'isAiDrafted' => $update->is_ai_drafted,
                    'isPublished' => $update->isPublished(),
                    'awaitingApproval' => $update->isAwaitingApproval(),
                    'author' => $update->author?->name,
                    'publishedAt' => $update->published_at?->toIso8601String(),
                    'createdAt' => $update->created_at?->toIso8601String(),
                ])->values(),
            ],
            'statuses' => $this->options(IncidentStatus::cases()),
            'impacts' => $this->options(IncidentImpact::cases()),
            'can' => [
                'update' => Gate::allows('update', $incident),
                'comment' => Gate::allows('comment', $incident),
                'delete' => Gate::allows('delete', $incident),
            ],
        ]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $incident = DB::transaction(function () use ($validated, $request): Incident {
            $incident = Incident::create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'status' => $validated['status'],
                'impact' => $validated['impact'],
                'is_published' => $validated['is_published'] ?? true,
                'started_at' => now(),
            ]);

            IncidentUpdate::create([
                'incident_id' => $incident->id,
                'author_id' => $request->user()->id,
                'status' => $validated['status'],
                'body' => $validated['body'],
                'published_at' => now(),
            ]);

            $this->syncAffectedComponents($incident, $validated);

            return $incident;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Incident opened.')]);

        return to_route('workspace.incidents.show', ['incident' => $incident]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validated();
        $status = IncidentStatus::from($validated['status']);

        $incident->fill($validated);

        // Resolving stamps the clock once; reopening clears it, so the public
        // timeline never claims an incident ended before its last update.
        $incident->resolved_at = $status->isTerminal()
            ? ($incident->resolved_at ?? now())
            : null;

        $incident->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Incident updated.')]);

        return back();
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        Gate::authorize('delete', $incident);

        $incident->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Incident deleted.')]);

        return to_route('workspace.incidents.index');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncAffectedComponents(Incident $incident, array $validated): void
    {
        $componentIds = $validated['component_ids'] ?? [];

        if ($componentIds === []) {
            return;
        }

        $status = $validated['component_status'];

        $incident->components()->sync(
            collect($componentIds)
                ->mapWithKeys(fn (int|string $id): array => [
                    (int) $id => ['tenant_id' => $incident->tenant_id, 'status' => $status],
                ])
                ->all(),
        );

        Component::query()->whereIn('id', $componentIds)->each(
            fn (Component $component) => $component->update(['status' => $status]),
        );
    }

    /**
     * Slugs are unique per tenant, and RLS scopes the uniqueness probe to the
     * current tenant automatically.
     */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'incident';
        $slug = $base;
        $suffix = 1;

        while (Incident::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }

    /**
     * @param  list<ComponentStatus|IncidentStatus|IncidentImpact>  $cases
     * @return list<array{value: string, label: string}>
     */
    private function options(array $cases): array
    {
        return array_map(
            fn ($case): array => ['value' => $case->value, 'label' => $case->label()],
            $cases,
        );
    }
}
