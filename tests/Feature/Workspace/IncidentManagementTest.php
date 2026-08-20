<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Enums\MembershipRole;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Incident lifecycle
|--------------------------------------------------------------------------
|
| The behaviour the product is sold on: opening an incident says something,
| posting an update moves the page, and a draft changes nothing until someone
| approves it.
|
*/

beforeEach(function () {
    [$this->tenant, $this->user] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create();
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'organization_id' => null,
            'role' => MembershipRole::Owner,
        ]);

        return [$tenant, $user];
    });

    $this->base = "/workspaces/{$this->tenant->id}";
});

it('opens an incident together with its first update', function () {
    $this->actingAs($this->user)
        ->post("{$this->base}/incidents", [
            'title' => 'Elevated API error rate',
            'status' => IncidentStatus::Investigating->value,
            'impact' => IncidentImpact::Major->value,
            'body' => 'We are seeing elevated 500s and are investigating.',
        ])
        ->assertRedirect();

    $incident = asSuperAdmin(fn () => Incident::query()->with('updates')->first());

    expect($incident->title)->toBe('Elevated API error rate')
        ->and($incident->tenant_id)->toBe($this->tenant->id)
        ->and($incident->slug)->toBe('elevated-api-error-rate')
        ->and($incident->started_at)->not->toBeNull()
        ->and($incident->updates)->toHaveCount(1)
        ->and($incident->updates->first()->body)->toContain('elevated 500s')
        ->and($incident->updates->first()->author_id)->toBe($this->user->id);
});

it('rejects an incident with no opening update', function () {
    $this->actingAs($this->user)
        ->post("{$this->base}/incidents", [
            'title' => 'Silent incident',
            'status' => IncidentStatus::Investigating->value,
            'impact' => IncidentImpact::Minor->value,
        ])
        ->assertSessionHasErrors('body');

    expect(asSuperAdmin(fn () => Incident::query()->count()))->toBe(0);
});

it('degrades the components an incident names', function () {
    $component = asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create());

    $this->actingAs($this->user)
        ->post("{$this->base}/incidents", [
            'title' => 'Webhook delays',
            'status' => IncidentStatus::Investigating->value,
            'impact' => IncidentImpact::Minor->value,
            'body' => 'Deliveries are queuing.',
            'component_ids' => [$component->id],
            'component_status' => ComponentStatus::PartialOutage->value,
        ])
        ->assertRedirect();

    $fresh = asSuperAdmin(fn () => $component->fresh());

    expect($fresh->status)->toBe(ComponentStatus::PartialOutage)
        ->and($fresh->status_changed_at)->not->toBeNull();
});

it('gives each incident a distinct slug within the tenant', function () {
    foreach (range(1, 2) as $ignored) {
        $this->actingAs($this->user)->post("{$this->base}/incidents", [
            'title' => 'Repeated outage',
            'status' => IncidentStatus::Investigating->value,
            'impact' => IncidentImpact::Minor->value,
            'body' => 'Same title, different incident.',
        ]);
    }

    $slugs = asSuperAdmin(fn () => Incident::query()->pluck('slug')->all());

    expect($slugs)->toHaveCount(2)
        ->and($slugs)->toBe(['repeated-outage', 'repeated-outage-2']);
});

it('moves the incident status when an update is published', function () {
    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create([
        'status' => IncidentStatus::Investigating,
    ]));

    $this->actingAs($this->user)
        ->post("{$this->base}/incidents/{$incident->id}/updates", [
            'status' => IncidentStatus::Resolved->value,
            'body' => 'Everything is back to normal.',
            'publish' => true,
        ])
        ->assertRedirect();

    $fresh = asSuperAdmin(fn () => $incident->fresh());

    expect($fresh->status)->toBe(IncidentStatus::Resolved)
        ->and($fresh->resolved_at)->not->toBeNull();
});

it('leaves the incident untouched while an update stays a draft', function () {
    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create([
        'status' => IncidentStatus::Investigating,
    ]));

    $this->actingAs($this->user)
        ->post("{$this->base}/incidents/{$incident->id}/updates", [
            'status' => IncidentStatus::Resolved->value,
            'body' => 'Draft resolution, not yet approved.',
            'publish' => false,
        ])
        ->assertRedirect();

    $fresh = asSuperAdmin(fn () => $incident->fresh());
    $update = asSuperAdmin(fn () => IncidentUpdate::query()->latest('id')->first());

    expect($fresh->status)->toBe(IncidentStatus::Investigating)
        ->and($fresh->resolved_at)->toBeNull()
        ->and($update->isPublished())->toBeFalse();
});

it('publishes a held draft in one request', function () {
    [$incident, $draft] = asSuperAdmin(function () {
        $incident = Incident::factory()->forTenant($this->tenant)->create([
            'status' => IncidentStatus::Monitoring,
        ]);

        return [$incident, IncidentUpdate::factory()->forTenant($this->tenant)->aiDraft()->create([
            'incident_id' => $incident->id,
            'status' => IncidentStatus::Resolved,
        ])];
    });

    expect($draft->isAwaitingApproval())->toBeTrue();

    $this->actingAs($this->user)
        ->post("{$this->base}/incidents/{$incident->id}/updates/{$draft->id}/publish")
        ->assertRedirect();

    expect(asSuperAdmin(fn () => $draft->fresh())->isPublished())->toBeTrue()
        ->and(asSuperAdmin(fn () => $incident->fresh())->status)->toBe(IncidentStatus::Resolved);
});

it('clears the resolved timestamp when an incident is reopened', function () {
    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->resolved()->create());

    expect($incident->resolved_at)->not->toBeNull();

    $this->actingAs($this->user)
        ->put("{$this->base}/incidents/{$incident->id}", [
            'title' => $incident->title,
            'impact' => $incident->impact->value,
            'status' => IncidentStatus::Investigating->value,
        ])
        ->assertRedirect();

    expect(asSuperAdmin(fn () => $incident->fresh())->resolved_at)->toBeNull();
});

it('will not publish an update belonging to another incident', function () {
    [$incidentA, $incidentB, $draft] = asSuperAdmin(function () {
        $a = Incident::factory()->forTenant($this->tenant)->create();
        $b = Incident::factory()->forTenant($this->tenant)->create();

        return [$a, $b, IncidentUpdate::factory()->forTenant($this->tenant)->aiDraft()->create([
            'incident_id' => $b->id,
        ])];
    });

    $this->actingAs($this->user)
        ->post("{$this->base}/incidents/{$incidentA->id}/updates/{$draft->id}/publish")
        ->assertNotFound();

    expect(asSuperAdmin(fn () => $draft->fresh())->isPublished())->toBeFalse();
});
