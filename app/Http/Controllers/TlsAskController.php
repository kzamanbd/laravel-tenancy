<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * The gate Caddy consults before obtaining a certificate for a hostname.
 *
 * On-demand TLS means Caddy will ask about *any* hostname pointed at it,
 * including ones we have never heard of. Answering 200 makes us obtain a
 * publicly trusted certificate for that name, so this endpoint is the only
 * thing standing between the product and being a convenient phishing host --
 * free tier included.
 *
 * A certificate is issued only when all of these hold:
 *  - the hostname is one we have a record of;
 *  - its owner has proven control of it via DNS;
 *  - the owning organization is on a plan that includes custom domains.
 *
 * Caddy treats any non-2xx as "do not issue", so every refusal is a 404: there
 * is no reason to tell an unauthenticated caller which hostnames we know about.
 *
 * The matching edge configuration:
 *
 *     {
 *         on_demand_tls {
 *             ask http://app:8000/internal/tls-ask
 *             interval 2m
 *             burst 5
 *         }
 *     }
 *
 *     https:// {
 *         tls { on_demand }
 *
 *         # Customer hostnames resolve to a page by Host, so the origin needs
 *         # no route per domain.
 *         rewrite * /status/by-host
 *         reverse_proxy app:8000
 *     }
 */
class TlsAskController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $hostname = strtolower(trim((string) $request->query('domain')));

        if ($hostname === '' || ! $this->looksLikeHostname($hostname)) {
            return response('', 404);
        }

        $domain = Domain::query()->where('domain', $hostname)->first();

        if ($domain === null) {
            return $this->refuse($hostname, 'unknown hostname');
        }

        if (! $domain->mayIssueCertificate()) {
            return $this->refuse($hostname, 'ownership not verified');
        }

        $tenant = Tenant::query()->with('organization')->find($domain->tenant_id);
        $plan = $tenant?->organization?->plan;

        if ($plan === null || ! $plan->allowsCustomDomain()) {
            return $this->refuse($hostname, 'plan does not include custom domains');
        }

        return response('', 200);
    }

    private function refuse(string $hostname, string $reason): Response
    {
        // Logged because a spike here is the signal that somebody is pointing
        // hostnames at the platform to see what sticks.
        Log::info('Declined certificate issuance', [
            'hostname' => $hostname,
            'reason' => $reason,
        ]);

        return response('', 404);
    }

    /**
     * Cheap shape check so obvious junk never reaches the database. Caddy is
     * asked about every connection attempt, including from scanners.
     */
    private function looksLikeHostname(string $value): bool
    {
        return strlen($value) <= 253
            && preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/', $value) === 1;
    }
}
