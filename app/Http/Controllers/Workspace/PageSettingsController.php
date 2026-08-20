<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\UpdatePageSettingsRequest;
use App\Models\Component;
use App\Models\Tenant;
use App\Services\StatusPagePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * How the public page looks, and the control for pushing it live.
 */
class PageSettingsController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('viewAny', Component::class);

        /** @var Tenant $tenant */
        $tenant = tenant();

        return Inertia::render('workspace/settings', [
            'page' => [
                'name' => $tenant->name,
                'headline' => $tenant->headline,
                'supportUrl' => $tenant->support_url,
                'logoPath' => $tenant->logo_path,
                'primaryColor' => $tenant->primary_color,
                'customCss' => $tenant->custom_css,
                'timezone' => $tenant->timezone,
                'showPoweredBy' => $tenant->show_powered_by,
                'lastPublishedAt' => $tenant->last_published_at?->toIso8601String(),
                'publicUrl' => route('status-page.show', ['tenant' => $tenant->getTenantKey()]),
            ],
            'timezones' => timezone_identifiers_list(),
            'can' => ['update' => Gate::allows('create', Component::class)],
        ]);
    }

    public function update(UpdatePageSettingsRequest $request, StatusPagePublisher $publisher): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $tenant->forceFill($request->validated())->save();

        // Publish straight away rather than queueing: the operator is looking
        // at the result and expects the page to match what they just saved.
        $publisher->publish($tenant);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page settings saved and published.')]);

        return back();
    }

    /**
     * Republish without changing anything, for when storage was restored or a
     * template changed underneath the page.
     */
    public function publish(StatusPagePublisher $publisher): RedirectResponse
    {
        Gate::authorize('create', Component::class);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $publisher->publish($tenant);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Status page republished.')]);

        return back();
    }
}
