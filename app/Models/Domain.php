<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DomainVerificationStatus;
use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/**
 * A hostname a status page answers on -- either the platform subdomain or a
 * customer's own domain.
 *
 * Certificates are only ever issued for a domain whose ownership has been
 * proven by CNAME. Issuing on request instead would make free-tier signups a
 * phishing vector.
 *
 * @property int $id
 * @property string $domain
 * @property int $tenant_id
 * @property bool $is_primary
 * @property string|null $verification_token
 * @property DomainVerificationStatus $verification_status
 * @property Carbon|null $verified_at
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $certificate_issued_at
 * @property Carbon|null $certificate_expires_at
 */
#[Fillable(['domain', 'tenant_id', 'is_primary'])]
class Domain extends BaseDomain
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    /**
     * Mirrors the column default. Without it a freshly created domain holds a
     * null status until it is reloaded, and `mayIssueCertificate()` -- the
     * check standing between this platform and issuing certificates for
     * hostnames nobody proved -- throws instead of answering false.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'verification_status' => 'pending',
        'is_primary' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'verification_status' => DomainVerificationStatus::class,
            'verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'certificate_issued_at' => 'datetime',
            'certificate_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $domain): void {
            if ($domain->isCustom()) {
                $domain->verification_token ??= 'status-verify-'.Str::random(32);

                return;
            }

            // A subdomain of our own central domain resolves through DNS we
            // already control, so there is nothing for a customer to prove --
            // and leaving it pending would make the TLS gate refuse a
            // certificate for the platform's own hostname.
            $domain->verification_status = DomainVerificationStatus::Verified;
            $domain->verified_at ??= now();
        });
    }

    /**
     * A customer-owned domain, as opposed to a subdomain of the platform.
     */
    public function isCustom(): bool
    {
        $central = config('tenancy.central_domains');

        foreach ($central as $centralDomain) {
            if ($centralDomain !== null && str_ends_with($this->domain, '.'.$centralDomain)) {
                return false;
            }
        }

        return true;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === DomainVerificationStatus::Verified;
    }

    /**
     * Answers the on-demand TLS `ask` endpoint. A certificate is issued only
     * for a hostname this returns true for.
     */
    public function mayIssueCertificate(): bool
    {
        return $this->verification_status->allowsCertificateIssuance();
    }

    /**
     * The CNAME target the customer must point their domain at before
     * verification can succeed.
     */
    public function expectedCnameTarget(): string
    {
        return 'cname.'.config('tenancy.central_domains')[0];
    }

    public function markVerified(): void
    {
        $this->forceFill([
            'verification_status' => DomainVerificationStatus::Verified,
            'verified_at' => now(),
            'last_checked_at' => now(),
        ])->save();
    }

    public function markVerificationFailed(): void
    {
        $this->forceFill([
            'verification_status' => DomainVerificationStatus::Failed,
            'last_checked_at' => now(),
        ])->save();
    }
}
