<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Maintenance;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\StatusPagePublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| The published page
|--------------------------------------------------------------------------
|
| Two promises are under test. First, that reading a status page touches no
| database at all -- otherwise the page dies with the service it reports on,
| and the product's central claim is false. Second, that nothing held back in
| the admin leaks into a file served from a CDN.
|
*/

beforeEach(function () {
    Storage::fake('status_pages');

    $this->tenant = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create(['name' => 'Acme Platform', 'primary_color' => '#0ea5e9']));

    $this->publisher = app(StatusPagePublisher::class);
});

it('writes both a json snapshot and rendered html', function () {
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create(['name' => 'REST API']));

    $paths = $this->publisher->publish($this->tenant);

    Storage::disk('status_pages')->assertExists($paths['json']);
    Storage::disk('status_pages')->assertExists($paths['html']);

    expect(Storage::disk('status_pages')->get($paths['html']))->toContain('REST API')
        ->and($this->tenant->fresh()->last_published_at)->not->toBeNull();
});

it('serves the published page without touching the database', function () {
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create(['name' => 'REST API']));

    $this->publisher->publish($this->tenant);

    // The whole point of the pipeline: a status page that needs the database is
    // a status page that goes down with everything else.
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $response = $this->get("/status/{$this->tenant->id}");

    $response->assertOk()
        ->assertSee('REST API')
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

    expect($queries)->toBe([], 'Serving a published page issued database queries: '.implode(' | ', $queries));
});

it('serves the json snapshot without touching the database', function () {
    $this->publisher->publish($this->tenant);

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->getJson("/status/{$this->tenant->id}/status.json")
        ->assertOk()
        ->assertJsonPath('page.name', 'Acme Platform');

    expect($queries)->toBe([]);
});

it('404s for a page that has never been published', function () {
    $this->get("/status/{$this->tenant->id}")->assertNotFound();
});

it('rejects a non-numeric tenant segment rather than probing storage', function () {
    $this->get('/status/..%2F..%2Fetc')->assertNotFound();
});

it('omits unpublished incidents from the page', function () {
    asSuperAdmin(function () {
        Incident::factory()->forTenant($this->tenant)->create(['title' => 'Public incident']);
        Incident::factory()->forTenant($this->tenant)->unpublished()->create(['title' => 'Internal only']);
    });

    $this->publisher->publish($this->tenant);
    $html = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');

    expect($html)->toContain('Public incident')
        ->and($html)->not->toContain('Internal only');
});

it('omits unpublished incident updates from the page', function () {
    asSuperAdmin(function () {
        $incident = Incident::factory()->forTenant($this->tenant)->create();

        IncidentUpdate::factory()->forTenant($this->tenant)->create([
            'incident_id' => $incident->id,
            'body' => 'Approved and visible.',
        ]);

        IncidentUpdate::factory()->forTenant($this->tenant)->aiDraft()->create([
            'incident_id' => $incident->id,
            'body' => 'Unreviewed draft text.',
        ]);
    });

    $this->publisher->publish($this->tenant);
    $html = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');

    expect($html)->toContain('Approved and visible.')
        ->and($html)->not->toContain('Unreviewed draft text.');
});

it('omits components marked private', function () {
    asSuperAdmin(function () {
        Component::factory()->forTenant($this->tenant)->create(['name' => 'Visible API']);
        Component::factory()->forTenant($this->tenant)->private()->create(['name' => 'Internal Queue']);
    });

    $this->publisher->publish($this->tenant);
    $html = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');

    expect($html)->toContain('Visible API')
        ->and($html)->not->toContain('Internal Queue');
});

it('shows only upcoming and in-flight maintenance', function () {
    asSuperAdmin(function () {
        Maintenance::factory()->forTenant($this->tenant)->create(['title' => 'Upcoming window']);
        Maintenance::factory()->forTenant($this->tenant)->create([
            'title' => 'Finished window',
            'status' => MaintenanceStatus::Completed,
        ]);
    });

    $this->publisher->publish($this->tenant);
    $html = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');

    expect($html)->toContain('Upcoming window')
        ->and($html)->not->toContain('Finished window');
});

it('reports the worst component status as the page banner', function () {
    asSuperAdmin(function () {
        Component::factory()->forTenant($this->tenant)->create(['status' => ComponentStatus::Operational]);
        Component::factory()->forTenant($this->tenant)->create(['status' => ComponentStatus::PartialOutage]);
        Component::factory()->forTenant($this->tenant)->create(['status' => ComponentStatus::Operational]);
    });

    $snapshot = $this->publisher->snapshotFor($this->tenant);

    expect($snapshot['status']['value'])->toBe(ComponentStatus::PartialOutage->value)
        ->and($snapshot['status']['description'])->toBe('Partial system outage');
});

it('reports all clear when a page has no components at all', function () {
    $snapshot = $this->publisher->snapshotFor($this->tenant);

    expect($snapshot['status']['value'])->toBe(ComponentStatus::Operational->value);
});

it('renders without any external request', function () {
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create());

    $this->publisher->publish($this->tenant);
    $html = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');

    // A page that *fetches* a stylesheet, font, script, or image from
    // elsewhere breaks in exactly the conditions it exists for. The subscribe
    // form's action is not one of those: it is a user-initiated POST, and the
    // page renders perfectly without it ever being used.
    expect($html)->not->toMatch('/<script/i')
        ->and($html)->not->toMatch('/<link[^>]+href/i')
        ->and($html)->not->toMatch('/\bsrc\s*=\s*["\']https?:/i')
        ->and($html)->not->toMatch('/@import/i');
});

it('removes the published artefacts when a page is unpublished', function () {
    $this->publisher->publish($this->tenant);
    Storage::disk('status_pages')->assertExists('pages/'.$this->tenant->id.'/index.html');

    $this->publisher->unpublish($this->tenant);

    Storage::disk('status_pages')->assertMissing('pages/'.$this->tenant->id.'/index.html');
});

it('keeps one tenant\'s snapshot out of another\'s file', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create(['name' => 'Other Co']));

    asSuperAdmin(function () use ($other) {
        Component::factory()->forTenant($this->tenant)->create(['name' => 'Mine Only']);
        Component::factory()->forTenant($other)->create(['name' => 'Theirs Only']);
    });

    $this->publisher->publish($this->tenant);
    $this->publisher->publish($other);

    $mine = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');
    $theirs = Storage::disk('status_pages')->get('pages/'.$other->id.'/index.html');

    expect($mine)->toContain('Mine Only')->not->toContain('Theirs Only')
        ->and($theirs)->toContain('Theirs Only')->not->toContain('Mine Only');
});

it('cannot break out of the style element through custom css', function () {
    $this->tenant->forceFill([
        'custom_css' => 'body { color: red; } </style><script>alert(1)</script><style>',
    ])->save();

    $this->publisher->publish($this->tenant);
    $html = Storage::disk('status_pages')->get('pages/'.$this->tenant->id.'/index.html');

    expect($html)->not->toContain('<script')
        ->and($html)->toContain('color: red');
});

it('strips @import so the page pulls in nothing third-party', function () {
    $this->tenant->forceFill([
        'custom_css' => '@import url("https://evil.test/track.css"); body { color: blue; }',
    ])->save();

    $snapshot = $this->publisher->snapshotFor($this->tenant);

    expect($snapshot['page']['customCss'])->not->toContain('@import')
        ->and($snapshot['page']['customCss'])->not->toContain('evil.test')
        ->and($snapshot['page']['customCss'])->toContain('color: blue');
});
