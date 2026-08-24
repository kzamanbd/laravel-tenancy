<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\StatusPagePublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
|--------------------------------------------------------------------------
| Serving a page on a customer's own hostname
|--------------------------------------------------------------------------
|
| The edge forwards the original Host, and the origin resolves it through a
| pointer file written at publish time. Resolving through the `domains` table
| would be simpler and would silently put a database query back on the one path
| that must not have one.
|
*/

beforeEach(function () {
    Storage::fake('status_pages');

    $this->tenant = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create(['name' => 'Acme Platform']));

    $this->publisher = app(StatusPagePublisher::class);
});

it('serves the right page for a verified hostname, with no queries', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()
        ->create(['domain' => 'status.acme-corp.test']));

    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create(['name' => 'Checkout API']));

    $this->publisher->publish($this->tenant);

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get("http://{$domain->domain}/status/by-host")
        ->assertOk()
        ->assertSee('Checkout API');

    expect($queries)->toBe([], 'Serving a custom domain issued queries: '.implode(' | ', $queries));
});

it('serves the json snapshot by host without queries', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()->create());

    $this->publisher->publish($this->tenant);

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->getJson("http://{$domain->domain}/status/by-host/status.json")
        ->assertOk()
        ->assertJsonPath('page.name', 'Acme Platform');

    expect($queries)->toBe([]);
});

it('does not serve a hostname that was never verified', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->create());

    $this->publisher->publish($this->tenant);

    // Pending verification means no pointer, so the hostname resolves nowhere
    // even though the page itself is published.
    $this->get("http://{$domain->domain}/status/by-host")->assertNotFound();
});

it('does not serve an unknown hostname', function () {
    $this->publisher->publish($this->tenant);

    $this->get('http://someone-elses-domain.test/status/by-host')->assertNotFound();
});

it('keeps each hostname pointed at its own page', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create(['name' => 'Other Co']));

    [$mine, $theirs] = asSuperAdmin(fn () => [
        Domain::factory()->forTenant($this->tenant)->custom()->verified()->create(['domain' => 'mine.test']),
        Domain::factory()->forTenant($other)->custom()->verified()->create(['domain' => 'theirs.test']),
    ]);

    asSuperAdmin(function () use ($other) {
        Component::factory()->forTenant($this->tenant)->create(['name' => 'Mine Only']);
        Component::factory()->forTenant($other)->create(['name' => 'Theirs Only']);
    });

    $this->publisher->publish($this->tenant);
    $this->publisher->publish($other);

    $this->get("http://{$mine->domain}/status/by-host")
        ->assertSee('Mine Only')->assertDontSee('Theirs Only');

    $this->get("http://{$theirs->domain}/status/by-host")
        ->assertSee('Theirs Only')->assertDontSee('Mine Only');
});

it('stops resolving once a domain loses verification', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()->create());

    $this->publisher->publish($this->tenant);
    $this->get("http://{$domain->domain}/status/by-host")->assertOk();

    asSuperAdmin(fn () => $domain->markVerificationFailed());
    $this->publisher->publish($this->tenant);

    $this->get("http://{$domain->domain}/status/by-host")->assertNotFound();
});

it('matches hostnames case-insensitively', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()
        ->create(['domain' => 'status.acme-corp.test']));

    $this->publisher->publish($this->tenant);

    $this->get('http://'.strtoupper($domain->domain).'/status/by-host')->assertOk();
});

it('answers a tenant hostname at the root with its status page, and no queries', function () {
    // A reader mid-outage types the hostname, nothing more. Making them find
    // /status/{id} first is the sort of friction a status page exists to remove.
    asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create(['name' => 'Checkout API']));

    $this->publisher->publish($this->tenant);

    $host = asSuperAdmin(fn () => $this->tenant->domains()->firstOrFail()->domain);

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get("http://{$host}/")
        ->assertOk()
        ->assertSee('Checkout API');

    // The root of a status page carries the same promise as the rest of the
    // read path: it must survive the database being gone.
    expect($queries)->toBeEmpty();
});

it('serves the snapshot at the root as status.json', function () {
    $this->publisher->publish($this->tenant);

    $host = asSuperAdmin(fn () => $this->tenant->domains()->firstOrFail()->domain);

    $this->getJson("http://{$host}/status.json")
        ->assertOk()
        ->assertJsonPath('page.name', 'Acme Platform');
});

it('404s at the root of a host with no published page', function () {
    $host = asSuperAdmin(fn () => $this->tenant->domains()->firstOrFail()->domain);

    $this->get("http://{$host}/")->assertNotFound();
});

it('keeps the marketing page on the central domain', function () {
    // The platform's own host is not a status page, and its `/` is
    // domain-constrained so it still matches first.
    $this->get('http://'.config('tenancy.central_domains')[0].'/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('welcome'));
});
