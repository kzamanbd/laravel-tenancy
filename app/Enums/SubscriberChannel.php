<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriberChannel: string
{
    case Email = 'email';
    case Slack = 'slack';
    case Webhook = 'webhook';
    case Sms = 'sms';
    case Discord = 'discord';
    case Teams = 'teams';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Slack => 'Slack',
            self::Webhook => 'Webhook',
            self::Sms => 'SMS',
            self::Discord => 'Discord',
            self::Teams => 'Microsoft Teams',
        };
    }

    /**
     * Whether the endpoint must be confirmed before anything is delivered to
     * it. Shared sending reputation makes this mandatory for email.
     */
    public function requiresDoubleOptIn(): bool
    {
        return in_array($this, [self::Email, self::Sms], strict: true);
    }

    /**
     * SMS is metered rather than bundled; it is the one line item that can
     * invert the gross margin.
     */
    public function isMetered(): bool
    {
        return $this === self::Sms;
    }
}
