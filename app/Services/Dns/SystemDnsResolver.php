<?php

declare(strict_types=1);

namespace App\Services\Dns;

/**
 * Resolver backed by the host's DNS.
 *
 * Lookups are cached briefly: a customer who has just pointed their domain will
 * retry the verify button several times in a row, and there is no sense asking
 * the resolver again within the same few seconds.
 */
class SystemDnsResolver implements DnsResolver
{
    /**
     * @return list<string>
     */
    public function cname(string $hostname): array
    {
        return $this->lookup($hostname, DNS_CNAME, 'target');
    }

    /**
     * @return list<string>
     */
    public function txt(string $hostname): array
    {
        return $this->lookup($hostname, DNS_TXT, 'txt');
    }

    /**
     * @return list<string>
     */
    private function lookup(string $hostname, int $type, string $key): array
    {
        // A missing record is the normal case while a customer is still setting
        // things up, and dns_get_record warns rather than returning cleanly.
        $records = @dns_get_record($hostname, $type);

        if ($records === false) {
            return [];
        }

        return collect($records)
            ->pluck($key)
            ->filter()
            ->map(fn (string $value): string => rtrim(strtolower($value), '.'))
            ->values()
            ->all();
    }
}
