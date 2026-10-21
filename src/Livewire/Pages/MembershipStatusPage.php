<?php

namespace jfsullivan\CommunityManager\Livewire\Pages;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use jfsullivan\CommunityManager\Mail\MembershipRequestMessageMail;
use jfsullivan\CommunityManager\Models\Community;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Member-facing status page for a SPECIFIC community (route parameter: a
 * pending member's current community deliberately stays untouched). Reached
 * from the `community-member` middleware instead of a 403
 * (`membership_status.enabled`), or from an app's own links.
 *
 * The page only ever shows the viewer THEIR OWN status:
 *  - no membership row in that community → home with a notice (no leak)
 *  - confirmed member or owner           → switch into the community + dashboard
 *  - pending request                     → awaiting approval
 *  - rejected request                    → it wasn't approved
 *  - former (ended) member               → no longer a member
 *
 * It names the community's owner and lets the person send the owner and
 * admins a short message (`membership_status.mail`, Reply-To them, so the
 * admins' addresses stay private), at most `membership_status.daily_message_limit`
 * a day per community.
 */
class MembershipStatusPage extends Component
{
    public Community $viewedCommunity;

    #[Locked]
    public ?Carbon $requestedAt = null;

    /** 'pending', 'rejected' (request turned down) or 'ended' (no longer a member). */
    #[Locked]
    public string $state = 'pending';

    /** The note to the community's owner and admins. */
    public string $message = '';

    public bool $sent = false;

    // Accepting the route parameter explicitly: defining mount() turns off
    // Livewire's automatic route-parameter assignment.
    public function mount(int|string $community)
    {
        $user = Auth::user();
        $community = config('community-manager.community_model')::query()->findOrFail($community);

        if (! $user->isMember($community)) {
            session()->flash('notify', [
                'type' => 'warning',
                'title' => 'No membership',
                'message' => "You don't have a membership request for that community.",
            ]);

            return $this->redirectRoute('home', navigate: true);
        }

        if ($user->isConfirmedMember($community) || $user->ownsCommunity($community)) {
            // The dashboard renders the CURRENT community — switch first so a
            // confirmed member lands in the community they asked about.
            $user->switchCommunity($community);

            return $this->redirectRoute('community.dashboard', navigate: true);
        }

        $this->viewedCommunity = $community;

        // isMember() above guarantees the row.
        $membership = $community->memberships()->where('memberships.user_id', $user->getAuthIdentifier())->firstOrFail();

        $this->state = match (true) {
            $membership->getAttribute('start_at') === null && $membership->getAttribute('end_at') === null => 'pending',
            $membership->getAttribute('start_at') === null => 'rejected',
            default => 'ended',
        };

        $requestedAt = $membership->latestStatus()->created_at ?? $membership->getAttribute('created_at');
        $this->requestedAt = $this->state === 'pending' && $requestedAt instanceof Carbon ? $requestedAt : null;
    }

    /** One email to the owner and every current admin, with the person as Reply-To. */
    public function sendMessage(): void
    {
        $this->validate(
            ['message' => ['required', 'string', 'max:2000']],
            ['message.required' => 'Write a short message for the community admins.'],
        );

        $user = Auth::user();
        $recipients = $this->viewedCommunity->administrators();

        if ($recipients->isEmpty()) {
            $this->addError('message', 'This community has no admins to contact.');

            return;
        }

        $key = 'membership-message:'.$user->getAuthIdentifier().':'.$this->viewedCommunity->id;

        if (RateLimiter::tooManyAttempts($key, (int) config('community-manager.membership_status.daily_message_limit', 3))) {
            $this->addError('message', 'You\'ve already sent the admins a few messages today. Please give them a little time to reply.');

            return;
        }

        RateLimiter::hit($key, 86400);

        // All on one email, so the admins see each other and reply once.
        $mail = config('community-manager.membership_status.mail', MembershipRequestMessageMail::class);
        Mail::to($recipients)->send(new $mail($user, $this->viewedCommunity, $this->message, $this->state));

        $this->reset('message');
        $this->sent = true;
        $this->dispatch('membership-message-sent');
    }

    public function render()
    {
        return view('community-manager::livewire.pages.membership-status-page', ['community' => $this->viewedCommunity])
            ->layout('components.layouts.app');
    }
}
