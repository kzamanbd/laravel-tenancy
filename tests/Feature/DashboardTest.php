<?php

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('an outsider cannot read a tenant dashboard by typing its subdomain', function () {
    // The dashboard reports on whichever tenant the host resolved to. Row-Level
    // Security scopes the queries to that tenant and hands the rows over --
    // correctly, because it was asked to. Nothing but the membership gate stops
    // a stranger reading another organization's component counts, open
    // incidents, and subscriber totals.
    $tenant = asSuperAdmin(fn () => Tenant::factory()->create());
    $outsider = asSuperAdmin(fn () => User::factory()->create());

    $domain = $tenant->domains()->firstOrFail()->domain;

    $this->actingAs($outsider)
        ->get("http://{$domain}/dashboard")
        ->assertForbidden();
});

test('a member can read the dashboard of a tenant they belong to', function () {
    [$tenant, $member] = asSuperAdmin(function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'organization_id' => null,
            'role' => MembershipRole::Owner,
        ]);

        return [$tenant, $user];
    });

    $domain = $tenant->domains()->firstOrFail()->domain;

    $this->actingAs($member)
        ->get("http://{$domain}/dashboard")
        ->assertOk();
});

test('the central dashboard needs no tenant and shows the user their own portfolio', function () {
    // No tenant resolves on the central domain, so the gate passes through --
    // and the portfolio read filters explicitly by the user's own tenants.
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('overview.scope', 'portfolio'));
});

test('the portfolio lists exactly the pages the membership gate would allow', function () {
    // The pivot and the memberships table can disagree -- a `tenant_user` row
    // carries no role. Listing from the pivot would advertise pages that then
    // answer 403, and hide pages this user can actually open.
    [$user, $viaMembership, $viaPivotOnly] = asSuperAdmin(function () {
        $user = User::factory()->create();
        $granted = Tenant::factory()->create();
        $attachedOnly = Tenant::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $granted->id,
            'organization_id' => null,
            'role' => MembershipRole::Owner,
        ]);

        // Attached, but never granted a role.
        $user->tenants()->attach($attachedOnly);

        return [$user, $granted, $attachedOnly];
    });

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview.totals.pages', 1)
            ->where('overview.pages.0.id', $viaMembership->id));

    // And the page it left out really is closed to them.
    $domain = $viaPivotOnly->domains()->firstOrFail()->domain;

    $this->actingAs($user)
        ->get("http://{$domain}/dashboard")
        ->assertForbidden();
});
