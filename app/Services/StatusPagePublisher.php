<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\BuildStatusPageSnapshot;
use App\Models\Tenant;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * Renders a status page to flat files and writes them to object storage.
 *
 * The write path is the only part of this product allowed to fail. Once these
 * files exist, serving the page needs neither this application nor its
 * database, which is what lets the page stay honest during the outage it is
 * reporting.
 */
class StatusPagePublisher
{
    public function __construct(private BuildStatusPageSnapshot $snapshot) {}

    /**
     * Publish one page, returning the paths written.
     *
     * @return array{json: string, html: string}
     */
    public function publish(Tenant $tenant): array
    {
        $snapshot = ($this->snapshot)($tenant);

        $html = View::make('status-page.show', ['snapshot' => $snapshot])->render();
        $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $disk = $this->disk();
        $prefix = self::pathFor($tenant);

        // JSON first: it is what the widget, the API, and any future client
        // read. If the HTML write fails afterwards the page is stale rather
        // than half-rendered.
        $disk->put("{$prefix}/status.json", $json);
        $disk->put("{$prefix}/index.html", $html);

        $tenant->forceFill(['last_published_at' => now()])->save();

        return [
            'json' => "{$prefix}/status.json",
            'html' => "{$prefix}/index.html",
        ];
    }

    /**
     * Remove a page's published artefacts, for when a tenant is deleted or
     * unpublished. A status page left serving after its owner is gone is worse
     * than a 404.
     */
    public function unpublish(Tenant $tenant): void
    {
        $this->disk()->deleteDirectory(self::pathFor($tenant));
    }

    public function snapshotFor(Tenant $tenant): array
    {
        return ($this->snapshot)($tenant);
    }

    /**
     * Addressed by tenant key rather than slug: a slug can be edited, and a
     * published page must not silently move because someone renamed it.
     */
    public static function pathFor(Tenant $tenant): string
    {
        return 'pages/'.$tenant->getTenantKey();
    }

    private function disk(): Filesystem
    {
        return Storage::disk('status_pages');
    }
}
