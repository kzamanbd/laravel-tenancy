<?php

use App\Http\Controllers\PublishedStatusPageController;
use App\Http\Controllers\SubscriptionController;
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
            // A tenant's own hostname *is* its status page: acme.example.com
            // answers with the page itself rather than a marketing shell that
            // makes a reader hunt for it mid-outage.
            //
            // Registered after the central domain's `/`, which is
            // domain-constrained and so still matches first on the platform's
            // own host.
            Route::get('/', [PublishedStatusPageController::class, 'byHost'])
                ->name('status-page.root');

            Route::get('status.json', [PublishedStatusPageController::class, 'byHostJson'])
                ->name('status-page.root.json');

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

            // The published page is a static file, so its subscribe form posts
            // back here. Throttled by IP: this endpoint sends mail to an
            // address supplied by an anonymous caller.
            Route::post('status/{tenant}/subscribe', [SubscriptionController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('subscriptions.store');

            // Reached from a link in an email, so no session and no CSRF token
            // exists. The token in the URL is the authorisation.
            Route::get('subscriptions/confirm/{token}', [SubscriptionController::class, 'confirm'])
                ->middleware('throttle:20,1')
                ->name('subscriptions.confirm');

            Route::get('subscriptions/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])
                ->middleware('throttle:20,1')
                ->name('subscriptions.unsubscribe');

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
