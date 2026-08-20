<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Confirms the authenticated user actually belongs to the tenant that tenancy
 * just resolved.
 *
 * This is the counterpart to path-based tenant identification. That middleware
 * takes the tenant id straight from the URL and initializes it — it performs no
 * authorization of its own. Row-Level Security then faithfully scopes every
 * query to whichever tenant was named, which is precisely the problem: without
 * this check, editing the id in the address bar would walk another
 * organization's status pages with the database's full cooperation.
 *
 * Must run *after* `InitializeTenancyByPath`, which forgets the route parameter
 * once resolved, so the tenant is read from the tenancy context rather than the
 * request.
 */
class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        abort_if($tenant === null, 404);

        $user = $request->user();

        abort_if($user === null, 403);

        /** @var Tenant $tenant */
        abort_unless($user->canAccessTenant($tenant), 403);

        return $next($request);
    }
}
