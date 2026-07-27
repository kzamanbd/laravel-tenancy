<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'tenant' => tenant() ? [
                'id' => tenant('id'),
                'name' => tenant('name'),
            ] : null,
        ]);
    }
}
