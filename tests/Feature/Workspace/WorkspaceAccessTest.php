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
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Workspace access control
|--------------------------------------------------------------------------
|
| Path-based tenancy takes the tenant id straight from the URL and performs no
| authorization of its own; Row-Level Security then scopes the request to
| exactly the tenant that was named. That combination is only safe because
| EnsureUserBelongsToTenant sits behind the identification middleware.
|
| These tests exist to prove that ordering holds. If the middleware is ever
| reordered or dropped, editing an id in the address bar walks another
| organization's pages with the database's full cooperation.
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
function workspacePaths(int $tenantId): array
{
    return [
        "/workspaces/{$tenantId}/components",
        "/workspaces/{$tenantId}/incidents",
        "/workspaces/{$tenantId}/maintenance",
    ];
}

it('lets a member reach their own workspace', function () {
    foreach (workspacePaths($this->tenantA->id) as $path) {
        $this->actingAs($this->member)->get($path)->assertOk();
    }
});

it('forbids a member from reaching another organization\'s workspace by id', function () {
    foreach (workspacePaths($this->tenantB->id) as $path) {
        $this->actingAs($this->member)->get($path)->assertForbidden();
    }
});

it('forbids a user with no membership anywhere', function () {
    $stranger = asSuperAdmin(fn () => User::factory()->create());

    foreach (workspacePaths($this->tenantA->id) as $path) {
        $this->actingAs($stranger)->get($path)->assertForbidden();
    }
});

it('redirects a guest to log in rather than resolving the tenant', function () {
    $this->get("/workspaces/{$this->tenantA->id}/components")
        ->assertRedirect(route('login'));
});

it('404s on a tenant id that does not exist', function () {
    $this->actingAs($this->member)
        ->get('/workspaces/999999/components')
        ->assertNotFound();
});

it('cannot write into another organization\'s workspace', function () {
    $this->actingAs($this->member)
        ->post("/workspaces/{$this->tenantB->id}/components", [
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
        ->get("/workspaces/{$this->tenantA->id}/incidents/{$foreign->id}")
        ->assertNotFound();
});

it('cannot delete another workspace\'s maintenance window', function () {
    $foreign = asSuperAdmin(fn () => Maintenance::factory()->forTenant($this->tenantB)->create());

    $this->actingAs($this->member)
        ->delete("/workspaces/{$this->tenantA->id}/maintenance/{$foreign->id}")
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
        ->get("/workspaces/{$this->tenantA->id}/components")
        ->assertOk();

    $this->actingAs($orgMember)
        ->get("/workspaces/{$this->tenantB->id}/components")
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
        ->get("/workspaces/{$this->tenantA->id}/components")
        ->assertOk();

    $this->actingAs($viewer)
        ->post("/workspaces/{$this->tenantA->id}/components", [
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
        ->post("/workspaces/{$this->tenantA->id}/components", [
            'name' => 'Search',
            'status' => 'operational',
        ])
        ->assertRedirect();

    $this->actingAs($editor)
        ->delete("/workspaces/{$this->tenantA->id}/components/{$component->id}")
        ->assertForbidden();
});

it('keeps the membership gate registered behind path identification', function () {
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

        $identification = array_search(InitializeTenancyByPath::class, $middleware, strict: true);
        $gate = array_search(EnsureUserBelongsToTenant::class, $middleware, strict: true);

        expect($identification)->not->toBeFalse("[{$route->getName()}] does not identify a tenant.")
            ->and($gate)->not->toBeFalse("[{$route->getName()}] is missing the membership gate.")
            ->and($gate)->toBeGreaterThan($identification,
                "[{$route->getName()}] gates membership before the tenant is resolved.");
    });
});

it('makes the creator an owner of the page they provision', function () {
    $creator = asSuperAdmin(fn () => User::factory()->create());

    $this->actingAs($creator)
        ->post('/tenants', ['name' => 'Fresh Page', 'subdomain' => 'fresh-page'])
        ->assertRedirect();

    $tenant = asSuperAdmin(fn () => Tenant::query()->where('name', 'Fresh Page')->firstOrFail());

    expect($creator->fresh()->roleFor($tenant))->toBe(MembershipRole::Owner);

    // And the page is reachable straight away, rather than 403-ing its author.
    $this->actingAs($creator)
        ->get("/workspaces/{$tenant->id}/components")
        ->assertOk();
});
