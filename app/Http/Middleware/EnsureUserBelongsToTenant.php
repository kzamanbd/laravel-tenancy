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
 * This is the counterpart to tenant identification. `InitializeTenancyByDomain`
 * resolves whichever tenant owns the requested host and performs no
 * authorization of its own. Row-Level Security then faithfully scopes every
 * query to that tenant, which is precisely the problem: without this check,
 * typing another organization's subdomain would walk their workspace with the
 * database's full cooperation.
 *
 * Must run *after* the identification middleware, which is what guarantees a
 * tenant exists by the time this runs -- when a host cannot be resolved,
 * tenancy throws and the request never reaches here.
 *
 * A resolved tenant is therefore required to be a tenant the user belongs to;
 * *no* tenant is allowed through, because that is the central-domain case. The
 * central domain exposes nothing tenant-scoped: with no tenant set every policy
 * evaluates to NULL, and the one cross-tenant read (the portfolio dashboard)
 * filters explicitly by the tenants the user is a member of.
 */
class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant === null) {
            return $next($request);
        }

        $user = $request->user();

        abort_if($user === null, 403);

        /** @var Tenant $tenant */
        abort_unless($user->canAccessTenant($tenant), 403);

        return $next($request);
    }
}
