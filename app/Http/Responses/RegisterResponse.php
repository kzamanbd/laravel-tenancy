<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false], 201);
        }

        $target = $tenantUrl ?? Fortify::redirects('register');

        // Registration happens on the central domain but lands the user on
        // their own subdomain, which is a different origin. An Inertia visit is
        // an XHR, and the browser will not let it follow a redirect across
        // origins -- the request dies and the user sits on the register form
        // wondering whether their account was created.
        //
        // A 409 with X-Inertia-Location tells the client to perform a full
        // window.location visit instead, which crosses origins the way a form
        // post always could. The session cookie follows because SESSION_DOMAIN
        // is scoped to the parent domain.
        if ($request->inertia() && $this->leavesCurrentOrigin($request, $target)) {
            return Inertia::location($target);
        }

        return redirect()->intended($target);
    }

    /**
     * Whether the redirect target sits on a different origin than the request.
     */
    private function leavesCurrentOrigin(Request $request, string $target): bool
    {
        $host = parse_url($target, PHP_URL_HOST);

        if ($host === null || $host === false) {
            return false;
        }

        $scheme = parse_url($target, PHP_URL_SCHEME) ?: $request->getScheme();

        return $host !== $request->getHost() || $scheme !== $request->getScheme();
    }
}
