<?php

namespace jfsullivan\CommunityManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\MemberManager\Mail\BaseMailable;
use jfsullivan\MemberManager\Mail\Concerns\SendsOnBehalfOfInviter;
use jfsullivan\MemberManager\Models\Invitation;
use jfsullivan\MemberManager\Support\InvitationMessageSanitizer;

class CommunityInvitation extends BaseMailable implements ShouldQueue
{
    use Queueable;
    use SendsOnBehalfOfInviter;
    use SerializesModels;

    /** Protected (not private) so subclasses keep them when queued mail is serialized. */
    protected $community;

    protected $invitation;

    protected $existing_user;

    public $app_name;

    public $community_name;

    public $owner_name;

    public $invitee_name;

    public $invitee_email;

    public function __construct(Community $community, Invitation $invitation)
    {
        $this->community = $community;
        $this->invitation = $invitation;

        $this->mailTemplate = $this->community->invitationMailTemplate;

        $this->app_name = config('app.name');
        $this->community_name = $community->name;
        $this->owner_name = $community->owner->name;
        $this->invitee_name = $invitation->name;
        $this->invitee_email = $invitation->email;
        $this->existing_user = ! empty($invitation->user_id);
    }

    public function getCommunityId(): int
    {
        return $this->community->id;
    }

    public function getInvitationId(): int
    {
        return $this->invitation->id;
    }

    protected function getInviter()
    {
        return $this->invitation->sentBy;
    }

    public function getHtmlLayout(): string
    {
        return view('community-manager::emails.community-invitation', [
            'existing_user' => $this->existing_user,
            'no_reply' => empty($this->mailTemplate->sender_email),
            'acceptUrl' => $this->invitation->generateAcceptUrl(),
            'registerUrl' => $this->invitation->generateRegisterUrl(),
            // Sanitized again here: the view renders it as HTML.
            'personalMessage' => InvitationMessageSanitizer::sanitize($this->invitation->message),
            'inviterName' => $this->getInviter()?->name,
            'communityDescription' => $this->communityDescription(),
        ])->render();
    }

    /**
     * The community's description as plain text, capped at 300 characters
     * (the view keeps its line breaks), or null when it has none.
     */
    protected function communityDescription(): ?string
    {
        $description = trim(strip_tags((string) $this->community->getAttribute('description')));

        return $description === '' ? null : Str::limit($description, 300);
    }

    // public function build()
    // {
    //     $mailTemplate = $this->getMailTemplate();

    //     return $this->from($mailTemplate->sender_email, $mailTemplate->sender_name);
    // }

    // /**
    //  * Build the message.
    //  *
    //  * @return $this
    //  */
    // public function build()
    // {
    //     return $this->markdown('brain-tools::mail.community-invitation', ['acceptUrl' => URL::signedRoute('community-invitations.accept', [
    //         'invitation' => $this->invitation,
    //     ])])->subject($this->community->name . ' Invitation')->from($this->owner->email, $this->owner->name);
    // }
}
