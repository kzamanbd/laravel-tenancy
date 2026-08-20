<?php

declare(strict_types=1);

namespace App\Enums;

enum ComponentStatus: string
{
    case Operational = 'operational';
    case DegradedPerformance = 'degraded_performance';
    case PartialOutage = 'partial_outage';
    case MajorOutage = 'major_outage';
    case UnderMaintenance = 'under_maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Operational => 'Operational',
            self::DegradedPerformance => 'Degraded Performance',
            self::PartialOutage => 'Partial Outage',
            self::MajorOutage => 'Major Outage',
            self::UnderMaintenance => 'Under Maintenance',
        };
    }

    /**
     * Ordering used to roll individual components up into the page-wide
     * banner: the worst status present wins.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Operational => 0,
            self::UnderMaintenance => 1,
            self::DegradedPerformance => 2,
            self::PartialOutage => 3,
            self::MajorOutage => 4,
        };
    }

    public function isOperational(): bool
    {
        return $this === self::Operational;
    }
}
