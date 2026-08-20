<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreIncidentUpdateRequest;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Posting to the incident timeline. This is the action the whole product exists
 * to make cheap, so it stays a single request: write the update and move the
 * incident's own status in one transaction.
 */
class IncidentUpdateController extends Controller
{
    public function store(StoreIncidentUpdateRequest $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validated();
        $status = IncidentStatus::from($validated['status']);
        $publish = $validated['publish'] ?? true;

        DB::transaction(function () use ($incident, $request, $validated, $status, $publish): void {
            IncidentUpdate::create([
                'incident_id' => $incident->id,
                'author_id' => $request->user()->id,
                'status' => $status,
                'body' => $validated['body'],
                'published_at' => $publish ? now() : null,
            ]);

            // An unpublished draft must not move the incident's public status;
            // only publishing does.
            if (! $publish) {
                return;
            }

            $incident->status = $status;
            $incident->resolved_at = $status->isTerminal()
                ? ($incident->resolved_at ?? now())
                : null;
            $incident->save();
        });

        return back();
    }

    /**
     * Approve a draft that was written but never published — the one click the
     * auto-updating wedge promises.
     */
    public function publish(Incident $incident, IncidentUpdate $update): RedirectResponse
    {
        Gate::authorize('comment', $incident);

        abort_unless($update->incident_id === $incident->id, 404);

        DB::transaction(function () use ($incident, $update): void {
            $update->update(['published_at' => now()]);

            $incident->status = $update->status;
            $incident->resolved_at = $update->status->isTerminal()
                ? ($incident->resolved_at ?? now())
                : null;
            $incident->save();
        });

        return back();
    }

    public function destroy(Incident $incident, IncidentUpdate $update): RedirectResponse
    {
        Gate::authorize('delete', $incident);

        abort_unless($update->incident_id === $incident->id, 404);

        $update->delete();

        return back();
    }
}
