<?php

namespace jfsullivan\CommunityManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use jfsullivan\CommunityManager\Models\Community;

/**
 * A note to a community's owner and admins from someone whose membership
 * request is waiting (or was turned down, or who is no longer a member), sent
 * from their membership status page. Replies go straight back to them
 * (Reply-To); the admins' addresses are never shown to the sender.
 */
class MembershipRequestMessageMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $senderName;

    public string $senderEmail;

    public string $communityName;

    public function __construct(Authenticatable $sender, Community $community, public string $body, public string $requestState)
    {
        $this->senderName = (string) ($sender->name ?? '');
        $this->senderEmail = (string) ($sender->email ?? '');
        $this->communityName = $community->name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: $this->senderName.' sent a message about joining '.$this->communityName,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'community-manager::emails.membership-request-message');
    }
}
