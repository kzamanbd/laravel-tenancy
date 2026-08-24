<?php

declare(strict_types=1);

namespace App\Enums;

enum Plan: string
{
    case Free = 'free';
    case Starter = 'starter';
    case Growth = 'growth';
    case Agency = 'agency';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function monthlyPriceInCents(): ?int
    {
        return match ($this) {
            self::Free => 0,
            self::Starter => 1900,
            self::Growth => 5900,
            self::Agency => 14900,
            self::Enterprise => null,
        };
    }

    /**
     * Every plan may point its own hostname at the platform, free included.
     *
     * This is a pricing decision, not a safety one. What stops the platform
     * being a convenient phishing host is that a certificate is issued only for
     * a hostname whose owner proved control of it by DNS -- see `DomainVerifier`
     * and `TlsAskController`. That proof is unchanged and unchangeable by plan;
     * a free customer still has to own the name they are pointing at us.
     *
     * Kept as a method rather than inlined so re-gating it later is one edit.
     */
    public function allowsCustomDomain(): bool
    {
        return true;
    }

    public function showsPoweredByBadge(): bool
    {
        return $this === self::Free;
    }

    /**
     * Null means unmetered.
     */
    public function componentLimit(): ?int
    {
        return match ($this) {
            self::Free => 5,
            self::Starter => 25,
            self::Growth, self::Agency => 100,
            self::Enterprise => null,
        };
    }

    public function subscriberLimit(): ?int
    {
        return match ($this) {
            self::Free => 50,
            self::Starter => 1_000,
            self::Growth => 5_000,
            self::Agency => 25_000,
            self::Enterprise => null,
        };
    }

    public function monitorLimit(): ?int
    {
        return match ($this) {
            self::Free => 0,
            self::Starter => 20,
            self::Growth, self::Agency => 100,
            self::Enterprise => null,
        };
    }
}
