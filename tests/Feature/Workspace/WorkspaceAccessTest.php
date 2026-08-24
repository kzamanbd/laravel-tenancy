<?php

declare(strict_types=1);

use App\Enums\MembershipRole;
use App\Http\Middleware\EnsureUserBelongsToTenant;
use App\Models\Component;
use App\Models\Incident;
use App\Models\Maintenance;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

/*
|--------------------------------------------------------------------------
| Workspace access control
|--------------------------------------------------------------------------
|
| Domain-based tenancy resolves whichever tenant owns the requested host and
| performs no authorization of its own; Row-Level Security then scopes the
| request to exactly that tenant. That combination is only safe because
| EnsureUserBelongsToTenant sits behind the identification middleware.
|
| These tests exist to prove that ordering holds. If the middleware is ever
| reordered or dropped, typing another organization's subdomain walks their
| pages with the database's full cooperation.
|
*/

beforeEach(function () {
    [$this->orgA, $this->orgB, $this->tenantA, $this->tenantB] = asSuperAdmin(function () {
        $orgA = Organization::factory()->create(['tenant_quota' => 5]);
        $orgB = Organization::factory()->create(['tenant_quota' => 5]);

        return [
            $orgA,
            $orgB,
            Tenant::factory()->forOrganization($orgA)->create(['name' => 'Tenant A']),
            Tenant::factory()->forOrganization($orgB)->create(['name' => 'Tenant B']),
        ];
    });

    $this->member = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenantA->id,
            'organization_id' => null,
            'role' => MembershipRole::Owner,
        ]);

        return $user;
    });
});

/** @return list<string> */
function workspacePaths(Tenant $tenant): array
{
    return [
        workspaceUrl($tenant, '/components'),
        workspaceUrl($tenant, '/incidents'),
        workspaceUrl($tenant, '/maintenance'),
    ];
}

it('lets a member reach their own workspace', function () {
    foreach (workspacePaths($this->tenantA) as $path) {
        $this->actingAs($this->member)->get($path)->assertOk();
    }
});

it('forbids a member from reaching another organization\'s workspace by hostname', function () {
    foreach (workspacePaths($this->tenantB) as $path) {
        $this->actingAs($this->member)->get($path)->assertForbidden();
    }
});

it('forbids a user with no membership anywhere', function () {
    $stranger = asSuperAdmin(fn () => User::factory()->create());

    foreach (workspacePaths($this->tenantA) as $path) {
        $this->actingAs($stranger)->get($path)->assertForbidden();
    }
});

it('redirects a guest to log in rather than resolving the tenant', function () {
    $response = $this->get(workspaceUrl($this->tenantA, '/components'));

    $response->assertRedirect();

    expect($response->headers->get('Location'))->toEndWith('/login');
});

it('404s on a hostname that belongs to no tenant', function () {
    $this->actingAs($this->member)
        ->get('http://nobody-owns-this.'.config('tenancy.central_domains')[0].'/workspaces/components')
        ->assertNotFound();
});

it('404s on the central domain, where a workspace has no tenant to resolve', function () {
    // The workspace is addressed by host, so these paths simply do not exist on
    // the central domain -- and answering 404 rather than 403 keeps them from
    // confirming anything about who exists.
    $this->actingAs($this->member)
        ->get('/workspaces/components')
        ->assertNotFound();
});

it('cannot write into another organization\'s workspace', function () {
    $this->actingAs($this->member)
        ->post(workspaceUrl($this->tenantB, '/components'), [
            'name' => 'Injected',
            'status' => 'operational',
        ])
        ->assertForbidden();

    $leaked = asSuperAdmin(
        fn () => Component::query()->where('tenant_id', $this->tenantB->id)->count(),
    );

    expect($leaked)->toBe(0);
});

it('cannot reach another workspace\'s incident through its own url', function () {
    $foreign = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenantB)->create());

    // The tenant in the path is one the user *can* reach, but the incident
    // belongs to another. RLS makes the record invisible, so binding 404s.
    $this->actingAs($this->member)
        ->get(workspaceUrl($this->tenantA, "/incidents/{$foreign->id}"))
        ->assertNotFound();
});

it('cannot delete another workspace\'s maintenance window', function () {
    $foreign = asSuperAdmin(fn () => Maintenance::factory()->forTenant($this->tenantB)->create());

    $this->actingAs($this->member)
        ->delete(workspaceUrl($this->tenantA, "/maintenance/{$foreign->id}"))
        ->assertNotFound();

    $survived = asSuperAdmin(fn () => Maintenance::query()->find($foreign->id));

    expect($survived)->not->toBeNull();
});

it('grants access through an organization-wide membership', function () {
    $orgMember = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => null,
            'organization_id' => $this->orgA->id,
            'role' => MembershipRole::Admin,
        ]);

        return $user;
    });

    $this->actingAs($orgMember)
        ->get(workspaceUrl($this->tenantA, '/components'))
        ->assertOk();

    $this->actingAs($orgMember)
        ->get(workspaceUrl($this->tenantB, '/components'))
        ->assertForbidden();
});

it('lets a viewer read but not write', function () {
    $viewer = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenantA->id,
            'organization_id' => null,
            'role' => MembershipRole::Viewer,
        ]);

        return $user;
    });

    $this->actingAs($viewer)
        ->get(workspaceUrl($this->tenantA, '/components'))
        ->assertOk();

    $this->actingAs($viewer)
        ->post(workspaceUrl($this->tenantA, '/components'), [
            'name' => 'Nope',
            'status' => 'operational',
        ])
        ->assertForbidden();
});

it('lets an editor write but not delete', function () {
    [$editor, $component] = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenantA->id,
            'organization_id' => null,
            'role' => MembershipRole::Editor,
        ]);

        return [$user, Component::factory()->forTenant($this->tenantA)->create()];
    });

    $this->actingAs($editor)
        ->post(workspaceUrl($this->tenantA, '/components'), [
            'name' => 'Search',
            'status' => 'operational',
        ])
        ->assertRedirect();

    $this->actingAs($editor)
        ->delete(workspaceUrl($this->tenantA, "/components/{$component->id}"))
        ->assertForbidden();
});

it('keeps the membership gate registered behind host identification', function () {
    // The behavioural tests above cannot distinguish the middleware from the
    // policies -- either layer alone returns 403, so removing one leaves the
    // suite green. Asserting the wiring directly is what catches a silent
    // regression, and the ordering matters: the gate reads the tenant that
    // identification resolved, so it is useless in front of it.
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (Illuminate\Routing\Route $route): bool => str_starts_with($route->getName() ?? '', 'workspace.'));

    expect($routes)->not->toBeEmpty();

    $routes->each(function (Illuminate\Routing\Route $route): void {
        $middleware = $route->gatherMiddleware();

        $identification = array_search(InitializeTenancyByDomain::class, $middleware, strict: true);
        $gate = array_search(EnsureUserBelongsToTenant::class, $middleware, strict: true);

        expect($identification)->not->toBeFalse("[{$route->getName()}] does not identify a tenant.")
            ->and($gate)->not->toBeFalse("[{$route->getName()}] is missing the membership gate.")
            ->and($gate)->toBeGreaterThan($identification,
                "[{$route->getName()}] gates membership before the tenant is resolved.")
            // The tenant comes from the host. A page id back in the path would
            // be a second, unauthenticated way to name a tenant.
            ->and($route->parameterNames())->not->toContain('tenant',
                "[{$route->getName()}] takes a tenant from the path.");
    });
});

it('lists only the pages the caller is a member of', function () {
    // The list carries names, member email addresses, and hostnames. Showing
    // every tenant told any authenticated user which organizations exist and
    // who works there.
    $response = $this->actingAs($this->member)->get('/tenants');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('tenants', 1)
        ->where('tenants.0.name', 'Tenant A'));
});

it('lists nothing for a user with no membership', function () {
    $stranger = asSuperAdmin(fn () => User::factory()->create());

    $this->actingAs($stranger)
        ->get('/tenants')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('tenants', 0));
});

it('lists every page in an organization to an organization-wide member', function () {
    [$orgMember, $second] = asSuperAdmin(function () {
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => null,
            'organization_id' => $this->orgA->id,
            'role' => MembershipRole::Admin,
        ]);

        return [$user, Tenant::factory()->forOrganization($this->orgA)->create(['name' => 'Tenant A2'])];
    });

    $this->actingAs($orgMember)
        ->get('/tenants')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('tenants', 2));

    expect($second->organization_id)->toBe($this->orgA->id);
});

it('makes the creator an owner of the page they provision', function () {
    $creator = asSuperAdmin(fn () => User::factory()->create());

    $this->actingAs($creator)
        ->post('/tenants', ['name' => 'Fresh Page', 'subdomain' => 'fresh-page'])
        ->assertRedirect();

    $tenant = asSuperAdmin(fn () => Tenant::query()->where('name', 'Fresh Page')->firstOrFail());

    expect($creator->fresh()->roleFor($tenant))->toBe(MembershipRole::Owner);

    // And the page is reachable straight away, rather than 403-ing its author.
    $tenant->load('domains');

    $this->actingAs($creator)
        ->get(workspaceUrl($tenant, '/components'))
        ->assertOk();
});
