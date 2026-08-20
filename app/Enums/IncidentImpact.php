<?php

declare(strict_types=1);

namespace App\Enums;

enum IncidentImpact: string
{
    case None = 'none';
    case Minor = 'minor';
    case Major = 'major';
    case Critical = 'critical';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function severity(): int
    {
        return match ($this) {
            self::None => 0,
            self::Minor => 1,
            self::Major => 2,
            self::Critical => 3,
        };
    }
}
