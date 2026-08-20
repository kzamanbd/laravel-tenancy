<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StatusPagePublisher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Serves an already-published status page straight from object storage.
 *
 * In production a CDN serves these objects directly and never reaches this
 * application. This controller is the origin behind that cache, and the local
 * stand-in for it.
 *
 * It must not touch the database. Not for route model binding, not for a
 * session, not for a feature flag. The whole promise of the publish pipeline is
 * that reading a status page survives the database being gone, and a single
 * stray query would quietly make that false -- `PublicStatusPageTest` asserts
 * the query count is zero.
 */
class PublishedStatusPageController extends Controller
{
    public function page(string $tenant): Response
    {
        return $this->serve($tenant, 'index.html', 'text/html; charset=UTF-8');
    }

    public function json(string $tenant): Response
    {
        return $this->serve($tenant, 'status.json', 'application/json');
    }

    /**
     * Serve whichever page is bound to the requesting hostname.
     *
     * The edge rewrites a request for a customer's own domain to this path and
     * forwards the original Host, which is resolved through a pointer file
     * written at publish time -- deliberately not through the `domains` table,
     * which would put a query back on the path that must not have one.
     */
    public function byHost(Request $request): Response
    {
        return $this->serve($this->tenantForHost($request), 'index.html', 'text/html; charset=UTF-8');
    }

    public function byHostJson(Request $request): Response
    {
        return $this->serve($this->tenantForHost($request), 'status.json', 'application/json');
    }

    private function tenantForHost(Request $request): string
    {
        // getHost() honours the trusted-proxy configuration, so a forwarded
        // host is only believed when it comes from the edge.
        $pointer = StatusPagePublisher::hostPointerFor($request->getHost());
        $disk = Storage::disk('status_pages');

        abort_unless($disk->exists($pointer), 404);

        return trim($disk->get($pointer));
    }

    private function serve(string $tenant, string $file, string $contentType): Response
    {
        // The tenant key is a bare integer. Validating its shape here keeps a
        // crafted path from escaping the prefix on the storage driver.
        abort_unless(ctype_digit($tenant), 404);

        $path = "pages/{$tenant}/{$file}";
        $disk = Storage::disk('status_pages');

        abort_unless($disk->exists($path), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => $contentType,
            // Short shared cache with a long stale window: during an incident a
            // fresh page matters, but serving a slightly old one always beats
            // serving an error.
            'Cache-Control' => 'public, max-age=30, s-maxage=30, stale-while-revalidate=300, stale-if-error=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
