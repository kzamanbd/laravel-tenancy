<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscriber;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class StatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public Tenant $page,
        public Subscriber $subscriber,
        public array $payload,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->page->name}] {$this->payload['heading']} — {$this->payload['status']}",
        );
    }

    /**
     * Mail clients surface `List-Unsubscribe` as a one-click button. Offering
     * it is what keeps an annoyed reader from reaching for "report spam"
     * instead, which would cost every tenant sharing this reputation.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.status-update',
            with: [
                'heading' => $this->payload['heading'],
                'body' => $this->payload['body'],
                'impact' => $this->payload['impact'],
                'components' => $this->payload['componentNames'],
                'pageName' => $this->page->name,
                'pageUrl' => route('status-page.show', ['tenant' => $this->page->getTenantKey()]),
                'unsubscribeUrl' => $this->unsubscribeUrl(),
            ],
        );
    }

    private function unsubscribeUrl(): string
    {
        return route('subscriptions.unsubscribe', ['token' => $this->subscriber->unsubscribe_token]);
    }
}
