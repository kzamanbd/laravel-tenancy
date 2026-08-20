<?php

declare(strict_types=1);

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('status_pages');

    [$this->tenant, $this->owner, $this->viewer] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create();

        $owner = User::factory()->create();
        Membership::factory()->role(MembershipRole::Owner)->create([
            'user_id' => $owner->id,
            'tenant_id' => $tenant->id,
        ]);

        $viewer = User::factory()->create();
        Membership::factory()->role(MembershipRole::Viewer)->create([
            'user_id' => $viewer->id,
            'tenant_id' => $tenant->id,
        ]);

        return [$tenant, $owner, $viewer];
    });

    $this->base = "/workspaces/{$this->tenant->id}";
});

it('saves theming and republishes in the same request', function () {
    $this->actingAs($this->owner)
        ->put("{$this->base}/settings", [
            'name' => 'Acme Status',
            'headline' => 'Live availability',
            'primary_color' => '#0ea5e9',
            'timezone' => 'Europe/London',
            'show_powered_by' => false,
        ])
        ->assertRedirect();

    $tenant = asSuperAdmin(fn () => $this->tenant->fresh());

    expect($tenant->name)->toBe('Acme Status')
        ->and($tenant->primary_color)->toBe('#0ea5e9')
        ->and($tenant->show_powered_by)->toBeFalse()
        ->and($tenant->last_published_at)->not->toBeNull();

    $html = Storage::disk('status_pages')->get("pages/{$this->tenant->id}/index.html");

    expect($html)->toContain('Acme Status')
        ->and($html)->toContain('#0ea5e9')
        // The badge is the growth loop, so turning it off has to actually work.
        ->and($html)->not->toContain('Powered by');
});

it('rejects a colour that is not a hex value', function () {
    $this->actingAs($this->owner)
        ->put("{$this->base}/settings", [
            'name' => 'Acme',
            'primary_color' => 'red; } body { display: none',
            'timezone' => 'UTC',
        ])
        ->assertSessionHasErrors('primary_color');
});

it('rejects an unknown timezone', function () {
    $this->actingAs($this->owner)
        ->put("{$this->base}/settings", [
            'name' => 'Acme',
            'primary_color' => '#000000',
            'timezone' => 'Mars/Olympus',
        ])
        ->assertSessionHasErrors('timezone');
});

it('refuses theming changes from a viewer', function () {
    $this->actingAs($this->viewer)
        ->put("{$this->base}/settings", [
            'name' => 'Hijacked',
            'primary_color' => '#000000',
            'timezone' => 'UTC',
        ])
        ->assertForbidden();

    expect(asSuperAdmin(fn () => $this->tenant->fresh())->name)->not->toBe('Hijacked');
});

it('republishes on demand without changing anything', function () {
    $this->actingAs($this->owner)->post("{$this->base}/publish")->assertRedirect();

    Storage::disk('status_pages')->assertExists("pages/{$this->tenant->id}/index.html");
});

it('refuses an on-demand republish from a viewer', function () {
    $this->actingAs($this->viewer)->post("{$this->base}/publish")->assertForbidden();

    Storage::disk('status_pages')->assertMissing("pages/{$this->tenant->id}/index.html");
});

it('publishes every page from the console', function () {
    asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    $this->artisan('status-page:publish')->assertSuccessful();

    Storage::disk('status_pages')->assertExists("pages/{$this->tenant->id}/index.html");
});

it('publishes a single page when given its id', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    $this->artisan('status-page:publish', ['tenant' => $this->tenant->id])->assertSuccessful();

    Storage::disk('status_pages')->assertExists("pages/{$this->tenant->id}/index.html");
    Storage::disk('status_pages')->assertMissing("pages/{$other->id}/index.html");
});
