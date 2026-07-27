<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        // CreateNewUser stashes the freshly-provisioned tenant URL so the user
        // lands on their own subdomain dashboard right after registering.
        $tenantUrl = $request->session()->pull('registered_tenant_url');

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 201)
            : redirect()->intended($tenantUrl ?? Fortify::redirects('register'));
    }
}
