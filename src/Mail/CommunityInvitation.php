<?php

namespace jfsullivan\CommunityManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\MemberManager\Models\Invitation;
use jfsullivan\MemberManager\Mail\BaseMailable;

class CommunityInvitation extends BaseMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private $community;

    private $invitation;

    private $existing_user;

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

    public function getHtmlLayout(): string
    {
        return view('community-manager::emails.community-invitation', [
            'existing_user' => $this->existing_user,
            'no_reply' => empty($this->mailTemplate->sender_email),
            'acceptUrl' => $this->invitation->generateAcceptUrl(),
            'registerUrl' => $this->invitation->generateRegisterUrl(),
        ])->render();
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
