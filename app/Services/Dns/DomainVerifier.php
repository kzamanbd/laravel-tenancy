<?php

declare(strict_types=1);

namespace App\Services\Dns;

use App\Enums\DomainVerificationStatus;
use App\Models\Domain;

/**
 * Proves a customer controls the hostname they are pointing at us.
 *
 * Nothing here issues a certificate; it only decides whether one may be issued
 * later. That order matters: a platform that will obtain a valid certificate
 * for any hostname somebody types is a phishing tool with a status page
 * attached, and the free tier makes it a free one.
 *
 * Either proof is accepted:
 *
 *  - a CNAME pointing at our target, which is required anyway for the page to
 *    resolve, so most customers verify simply by finishing setup; or
 *  - a TXT record carrying the domain's token, for customers whose apex record
 *    cannot be a CNAME.
 */
class DomainVerifier
{
    public function __construct(private DnsResolver $dns) {}

    public function verify(Domain $domain): bool
    {
        if ($this->hasProof($domain)) {
            $domain->markVerified();

            return true;
        }

        $domain->markVerificationFailed();

        return false;
    }

    public function hasProof(Domain $domain): bool
    {
        // Platform subdomains resolve through our own DNS; there is nothing for
        // the customer to prove.
        if (! $domain->isCustom()) {
            return true;
        }

        return $this->hasCname($domain) || $this->hasTxt($domain);
    }

    private function hasCname(Domain $domain): bool
    {
        $expected = strtolower($domain->expectedCnameTarget());

        return in_array($expected, $this->dns->cname($domain->domain), strict: true);
    }

    private function hasTxt(Domain $domain): bool
    {
        if ($domain->verification_token === null) {
            return false;
        }

        $token = strtolower($domain->verification_token);

        foreach ($this->dns->txt('_status-verify.'.$domain->domain) as $value) {
            if (str_contains($value, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * What the customer has to add, shown verbatim in the UI. Getting this
     * wrong generates support tickets forever, so it comes from the same place
     * the check reads.
     *
     * @return array{cname: array{name: string, value: string}, txt: array{name: string, value: string}}
     */
    public function instructionsFor(Domain $domain): array
    {
        return [
            'cname' => [
                'name' => $domain->domain,
                'value' => $domain->expectedCnameTarget(),
            ],
            'txt' => [
                'name' => '_status-verify.'.$domain->domain,
                'value' => (string) $domain->verification_token,
            ],
        ];
    }

    public function statusOf(Domain $domain): DomainVerificationStatus
    {
        return $domain->verification_status;
    }
}
