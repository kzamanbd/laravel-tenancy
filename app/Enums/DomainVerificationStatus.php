<?php

declare(strict_types=1);

namespace App\Enums;

enum DomainVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * The on-demand TLS `ask` endpoint issues a certificate only for a domain
     * in this state. Issuing before ownership is proven turns the free tier
     * into a phishing vector.
     */
    public function allowsCertificateIssuance(): bool
    {
        return $this === self::Verified;
    }
}
