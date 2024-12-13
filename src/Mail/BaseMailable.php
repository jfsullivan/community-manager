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

    public function envelope(): Envelope
    {
        $mailTemplate = $this->getMailTemplate();

        return new Envelope(
            from: new Address($mailTemplate->sender_email, $mailTemplate->sender_name),
            replyTo: [
                new Address($mailTemplate->sender_email, $mailTemplate->sender_name),
            ],
            subject: $mailTemplate->subject,
        );
    }
}
