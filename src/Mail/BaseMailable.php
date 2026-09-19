<?php

namespace jfsullivan\CommunityManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\MailTemplates\TemplateMailable;

class BaseMailable extends TemplateMailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The From address always stays on the app's authenticated sending
     * domain — mail providers (e.g. Resend) reject From addresses on domains
     * the API key is not verified for. The mail-template's configured sender
     * keeps the display name and receives replies via Reply-To.
     */
    public function envelope(): Envelope
    {
        $mailTemplate = $this->getMailTemplate();

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                $mailTemplate->sender_name ?: config('mail.from.name'),
            ),
            replyTo: $mailTemplate->sender_email
                ? [new Address($mailTemplate->sender_email, $mailTemplate->sender_name)]
                : [],
            subject: $mailTemplate->subject,
        );
    }
}
