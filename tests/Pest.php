<?php

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    // Saving a component or incident queues a republish, and the test queue
    // runs synchronously -- so without this, every feature test that touches
    // those models renders a real status page onto the developer's disk.
    ->beforeEach(fn () => Storage::fake('status_pages'))
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Provision fixtures with Row-Level Security suspended.
 *
 * Test setup is inherently cross-tenant: it seeds rows for tenant A and
 * tenant B before either is "logged in". That is exactly the privileged
 * position the escape hatch exists for, and routing setup through it keeps
 * the assertions themselves running under real policy enforcement.
 *
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function asSuperAdmin(callable $callback): mixed
{
    return app(TenantContext::class)->withoutIsolation($callback);
}

/**
 * Bind the connection to a tenant for the remainder of the test, the same way
 * a request arriving on that tenant's domain would.
 */
function actingAsTenant(Tenant $tenant): Tenant
{
    tenancy()->initialize($tenant);

    return $tenant;
}

/**
 * Drop back to the central context, where no tenant-scoped row is visible.
 */
function actingAsCentralDomain(): void
{
    tenancy()->end();

    app(TenantContext::class)->forget();
}

/**
 * Absolute URL of a tenant's workspace.
 *
 * Workspace routes resolve the tenant from the host, so a test has to request
 * them on that host -- a relative path would be answered by the central domain,
 * where these routes do not exist.
 */
function workspaceUrl(Tenant $tenant, string $path = ''): string
{
    $domain = $tenant->domains()->firstOrFail()->domain;

    return "http://{$domain}/workspaces".$path;
}
