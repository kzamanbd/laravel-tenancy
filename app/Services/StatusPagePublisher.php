<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\BuildStatusPageSnapshot;
use App\Enums\DomainVerificationStatus;
use App\Models\Domain;
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

        $this->syncHostPointers($tenant);

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
        foreach ($this->currentHostPointers($tenant) as $hostname) {
            $this->disk()->delete(self::hostPointerFor($hostname));
        }

        $this->disk()->deleteDirectory(self::pathFor($tenant));
    }

    /**
     * Writes a hostname -> tenant pointer for every verified domain.
     *
     * This is what lets a request arriving on a customer's own hostname find
     * its page without a database lookup. Resolving the host through the
     * `domains` table would work, and would also quietly reintroduce the
     * dependency the whole pipeline exists to remove.
     *
     * Pointers for hostnames that are no longer verified are deleted, so a
     * removed domain stops resolving here rather than serving a page its owner
     * no longer controls.
     */
    private function syncHostPointers(Tenant $tenant): void
    {
        $disk = $this->disk();

        $verified = Domain::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->where('verification_status', DomainVerificationStatus::Verified)
            ->pluck('domain')
            ->map(fn (string $domain): string => strtolower($domain))
            ->values()
            ->all();

        foreach (array_diff($this->currentHostPointers($tenant), $verified) as $stale) {
            $disk->delete(self::hostPointerFor($stale));
        }

        foreach ($verified as $hostname) {
            $disk->put(self::hostPointerFor($hostname), (string) $tenant->getTenantKey());
        }

        $disk->put(self::pathFor($tenant).'/hosts.json', json_encode($verified, JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<string>
     */
    private function currentHostPointers(Tenant $tenant): array
    {
        $path = self::pathFor($tenant).'/hosts.json';
        $disk = $this->disk();

        if (! $disk->exists($path)) {
            return [];
        }

        try {
            $hosts = json_decode($disk->get($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($hosts) ? array_values(array_filter($hosts, 'is_string')) : [];
    }

    public static function hostPointerFor(string $hostname): string
    {
        return 'hosts/'.strtolower($hostname);
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
