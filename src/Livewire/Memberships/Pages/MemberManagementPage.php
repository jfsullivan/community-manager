<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Pages;

use Illuminate\Support\Facades\Gate;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;
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
     * self-join / pool-derived requests that are awaiting approval.
     *
     * @return array<string, string>
     */
    public function statusFilterOptions(): array
    {
        return [
            'all' => 'All',
            'current' => 'Current',
            'pending' => 'Pending',
            'former' => 'Former',
        ];
    }

    /** Row partial adds Confirm/Reject affordances to pending community rows. */
    public function memberRowView(): string
    {
        return 'community-manager::livewire.memberships.partials.member-row';
    }

    /**
     * Confirm a pending membership: set start_at so the member gains access and
     * record the provenance in the status history.
     */
    public function confirmMembership($user_id): void
    {
        Gate::authorize('updateCommunityMember', $this->owningModel());

        $this->owningModel()->members()->sync([$user_id => ['start_at' => now()]], false);

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
     * Reject a pending membership by tombstoning it: record a `removed` status
     * and set end_at, WITHOUT deleting the row. Keeping the row makes a
     * prior-removed member detectable so a later pool join stays pool-only.
     */
    public function rejectMembership($user_id): void
    {
        Gate::authorize('updateCommunityMember', $this->owningModel());

        $this->owningModel()->memberships()
            ->where('user_id', $user_id)
            ->first()
            ?->setStatus('removed', 'rejected-by-admin');

        $this->owningModel()->members()->sync([$user_id => ['end_at' => now()]], false);

        $this->dispatch('notify',
            type: 'success',
            title: 'Request Rejected',
            message: 'The join request has been rejected.',
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
