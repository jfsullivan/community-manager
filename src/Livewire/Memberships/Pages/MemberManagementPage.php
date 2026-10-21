<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Pages;

use Illuminate\Support\Facades\Gate;
use jfsullivan\CommunityManager\Actions\RejectMembership;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\MemberManager\Actions\CreateInvitation;
use jfsullivan\MemberManager\Actions\SendInvitation;
use jfsullivan\MemberManager\Livewire\Pages\MemberManagementPage as BaseMemberManagementPage;

class MemberManagementPage extends BaseMemberManagementPage
{
    use ResolvesCommunity;

    public function layout(): string
    {
        return config('community-manager.admin_layout');
    }

    /**
     * Communities surface a `pending` segment so admins can review and confirm
     * join requests, and a `rejected` one for requests they turned down
     * (still confirmable from there).
     *
     * @return array<string, string>
     */
    public function statusFilterOptions(): array
    {
        return [
            'all' => 'All',
            'current' => 'Current',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
            'former' => 'Former',
            'banned' => 'Banned',
        ];
    }

    /**
     * Why join requests can't be confirmed right now, or null when they can:
     * the community's {@see Community::joinClosedReason()}.
     * Rejecting stays open.
     */
    public function confirmUnavailableReason(): ?string
    {
        return $this->owningModel()->joinClosedReason();
    }

    /** Why invitations can't be sent from the roster right now, or null when they can. */
    public function invitationsUnavailableReason(): ?string
    {
        return $this->owningModel()->invitationsClosedReason();
    }

    /** Send or resend a member's invitation from their row — refused while invitations are closed. */
    public function sendInvitation($member_id, $invitation_id, SendInvitation $sendInvitationAction, CreateInvitation $createInvitationAction)
    {
        $this->authorizeMemberManagement($this->owningModel());

        if (($unavailable = $this->invitationsUnavailableReason()) !== null) {
            $this->dispatch('notify', type: 'error', title: 'Invitations Closed', message: $unavailable);

            return;
        }

        return parent::sendInvitation($member_id, $invitation_id, $sendInvitationAction, $createInvitationAction);
    }

    /** Lifting a ban is a member-management act — same gate as confirm/reject. */
    protected function authorizeLiftBan($user_id): void
    {
        Gate::authorize('updateCommunityMember', $this->owningModel());
    }

    /** Row partial adds Confirm/Reject affordances to pending community rows. */
    public function memberRowView(): string
    {
        return 'community-manager::livewire.memberships.partials.member-row';
    }

    /**
     * Confirm a pending (or earlier rejected) request: set start_at so the
     * member gains access, clear end_at so an admin can change their mind
     * about a rejection, and record the provenance in the status history.
     */
    public function confirmMembership($user_id): void
    {
        Gate::authorize('updateCommunityMember', $this->owningModel());

        if (($unavailable = $this->confirmUnavailableReason()) !== null) {
            $this->dispatch('notify', type: 'error', title: 'Can’t Confirm', message: $unavailable);

            return;
        }

        $this->owningModel()->members()->sync([$user_id => ['start_at' => now(), 'end_at' => null]], false);

        $this->owningModel()->memberships()
            ->where('user_id', $user_id)
            ->first()
            ?->setStatus('joined', 'confirmed-by-admin');

        $this->dispatch('notify',
            type: 'success',
            title: 'Member Confirmed',
            message: 'The membership has been confirmed.',
        );

        $this->dispatch('refresh-members-list');
    }

    /**
     * Reject a pending request ({@see RejectMembership}): the row is kept as a
     * tombstone and the person is told.
     */
    public function rejectMembership($user_id): void
    {
        Gate::authorize('updateCommunityMember', $this->owningModel());

        $user = config('community-manager.user_model')::findOrFail($user_id);

        app(RejectMembership::class)->handle($this->owningModel(), $user);

        $this->dispatch('notify',
            type: 'success',
            title: 'Request Rejected',
            message: 'The join request has been rejected and they have been told.',
        );

        $this->dispatch('refresh-members-list');
    }

    public function layoutProperties(): array
    {
        return ['community' => $this->community];
    }

    public function breadcrumbsView(): ?string
    {
        return 'community-manager::livewire.memberships.partials.breadcrumbs';
    }

    public function memberDetailsUrl($member): ?string
    {
        return route('community.admin.members.show', ['user_id' => $member->user_id]);
    }
}
