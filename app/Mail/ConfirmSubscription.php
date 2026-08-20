<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscriber;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The one message sent to an unconfirmed address, and the only thing standing
 * between the subscribe form and it being usable to mail strangers.
 *
 * Queued so an anonymous request never waits on SMTP: the endpoint is public,
 * and a slow mail server would otherwise hold the connection open for anyone
 * who wanted to hold several.
 */
class ConfirmSubscription extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $page, public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirm your subscription to {$this->page->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.confirm-subscription',
            with: [
                'pageName' => $this->page->name,
                'confirmUrl' => route('subscriptions.confirm', ['token' => $this->subscriber->confirmation_token]),
            ],
        );
    }
}
