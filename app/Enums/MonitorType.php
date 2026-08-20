<?php

declare(strict_types=1);

namespace App\Enums;

enum MonitorType: string
{
    case Http = 'http';
    case Tcp = 'tcp';
    case Keyword = 'keyword';
    case Heartbeat = 'heartbeat';

    public function label(): string
    {
        return match ($this) {
            self::Http => 'HTTP',
            self::Tcp => 'TCP',
            self::Keyword => 'Keyword',
            self::Heartbeat => 'Cron Heartbeat',
        };
    }

    /**
     * A heartbeat monitor is inverted: the absence of an inbound ping is the
     * failure, so nothing is dialled outward.
     */
    public function isOutbound(): bool
    {
        return $this !== self::Heartbeat;
    }
}
