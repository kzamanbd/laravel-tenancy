<?php

declare(strict_types=1);

namespace App\Services\Dns;

/**
 * Reads DNS records for a hostname.
 *
 * Behind an interface so ownership verification can be exercised without a
 * network: the rules about what counts as proof are the part worth testing, and
 * they are untestable if every check has to hit a real resolver.
 */
interface DnsResolver
{
    /**
     * CNAME targets for a hostname, without the trailing dot.
     *
     * @return list<string>
     */
    public function cname(string $hostname): array;

    /**
     * TXT record values for a hostname.
     *
     * @return list<string>
     */
    public function txt(string $hostname): array;
}
