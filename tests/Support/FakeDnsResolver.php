<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\Dns\DnsResolver;

/**
 * In-memory DNS, so ownership rules can be tested without a resolver.
 */
class FakeDnsResolver implements DnsResolver
{
    /** @var array<string, list<string>> */
    private array $cnames = [];

    /** @var array<string, list<string>> */
    private array $txts = [];

    public function setCname(string $hostname, string ...$targets): self
    {
        $this->cnames[strtolower($hostname)] = array_map('strtolower', $targets);

        return $this;
    }

    public function setTxt(string $hostname, string ...$values): self
    {
        $this->txts[strtolower($hostname)] = array_map('strtolower', $values);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function cname(string $hostname): array
    {
        return $this->cnames[strtolower($hostname)] ?? [];
    }

    /**
     * @return list<string>
     */
    public function txt(string $hostname): array
    {
        return $this->txts[strtolower($hostname)] ?? [];
    }
}
