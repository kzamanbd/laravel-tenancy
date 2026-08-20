<?php

use App\Http\Controllers\PublishedStatusPageController;
use App\Http\Controllers\TlsAskController;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByPathException;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Registered outside the `web` group on purpose. Session, cookies,
            // and CSRF all reach for storage the published page must not need:
            // serving it has to work when the database is unavailable, and the
            // only way to keep that true is to give it no middleware that could
            // touch one.
            // Where the edge sends a request that arrived on a customer's own
            // hostname. Caddy rewrites to this path and forwards the Host, so
            // the origin never needs a route per domain.
            Route::get('status/by-host', [PublishedStatusPageController::class, 'byHost'])
                ->name('status-page.by-host');

            Route::get('status/by-host/status.json', [PublishedStatusPageController::class, 'byHostJson'])
                ->name('status-page.by-host.json');

            Route::get('status/{tenant}', [PublishedStatusPageController::class, 'page'])
                ->name('status-page.show');

            Route::get('status/{tenant}/status.json', [PublishedStatusPageController::class, 'json'])
                ->name('status-page.json');

            // Consulted by Caddy's on-demand TLS before it obtains a
            // certificate. Throttled because Caddy asks on every connection
            // attempt to an unknown name, scanners included.
            Route::get('internal/tls-ask', TlsAskController::class)
                ->middleware('throttle:60,1')
                ->name('tls.ask');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['themeConfig', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Used by stancl/tenancy for routes that resolve on both central and tenant domains.
        $middleware->group('universal', []);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A tenant that cannot be identified is a missing page, not a server
        // error -- and answering 404 rather than 403 keeps the workspace
        // routes from confirming which tenant ids exist.
        $exceptions->map(TenantCouldNotBeIdentifiedOnDomainException::class, fn () => abort(404, 'Tenant not found'));
        $exceptions->map(TenantCouldNotBeIdentifiedByPathException::class, fn () => abort(404, 'Tenant not found'));
    })->create();
