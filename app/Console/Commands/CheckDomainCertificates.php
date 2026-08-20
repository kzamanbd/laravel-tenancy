<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\Dns\DomainVerifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Watches connected domains for the two ways they quietly stop working.
 *
 * A customer removes the DNS record months later, or a certificate approaches
 * expiry and cannot be renewed because the hostname no longer points at us.
 * Both fail silently: the status page keeps looking fine in the dashboard while
 * visitors get a browser warning. Expired certificates on customer domains
 * generate support tickets forever, so this exists to notice first.
 */
class CheckDomainCertificates extends Command
{
    protected $signature = 'domains:check
                            {--expiring-within=14 : Warn about certificates expiring within this many days}';

    protected $description = 'Re-check DNS for connected domains and report certificates nearing expiry';

    public function handle(DomainVerifier $verifier): int
    {
        $days = (int) $this->option('expiring-within');

        $domains = Domain::query()
            ->whereNotNull('verified_at')
            ->get()
            ->filter(fn (Domain $domain): bool => $domain->isCustom());

        if ($domains->isEmpty()) {
            $this->components->info('No custom domains to check.');

            return self::SUCCESS;
        }

        $broken = 0;

        foreach ($domains as $domain) {
            $stillPointed = $verifier->hasProof($domain);

            if (! $stillPointed) {
                $broken++;

                // Deliberately not un-verifying: pulling the certificate on a
                // transient DNS blip would take the customer's page offline for
                // a lookup failure. Record it and let a human decide.
                $domain->forceFill(['last_checked_at' => now()])->save();

                Log::warning('Connected domain no longer resolves to us', [
                    'domain' => $domain->domain,
                    'tenant_id' => $domain->tenant_id,
                ]);

                $this->components->twoColumnDetail($domain->domain, '<fg=red>DNS no longer points here</>');

                continue;
            }

            $domain->forceFill(['last_checked_at' => now()])->save();

            $expiry = $domain->certificate_expires_at;

            if ($expiry !== null && $expiry->isBefore(now()->addDays($days))) {
                $this->components->twoColumnDetail(
                    $domain->domain,
                    '<fg=yellow>certificate expires '.$expiry->diffForHumans().'</>',
                );

                continue;
            }

            $this->components->twoColumnDetail($domain->domain, '<fg=green>ok</>');
        }

        if ($broken > 0) {
            $this->components->warn("{$broken} domain(s) no longer resolve to this platform.");
        }

        return self::SUCCESS;
    }
}
