<?php

namespace App\Http\Controllers;

use App\Actions\BuildDashboardOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BuildDashboardOverview $overview): Response
    {
        $tenant = tenant();

        return Inertia::render('dashboard', [
            'tenant' => $tenant ? ['id' => $tenant->getTenantKey(), 'name' => $tenant->name] : null,
            'overview' => $tenant
                ? $overview->forTenant($tenant)
                : $overview->forUser($request->user()),
        ]);
    }
}
