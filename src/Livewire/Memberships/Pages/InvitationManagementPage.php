<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Pages;

use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;
use jfsullivan\MemberManager\Livewire\Pages\InvitationManagementPage as BaseInvitationManagementPage;

class InvitationManagementPage extends BaseInvitationManagementPage
{
    use ResolvesCommunity;

    public function layout(): string
    {
        return config('community-manager.admin_layout');
    }

    public function layoutProperties(): array
    {
        return ['community' => $this->community];
    }

    public function breadcrumbsView(): ?string
    {
        return 'community-manager::livewire.memberships.partials.invitations-breadcrumbs';
    }

    public function membersIndexUrl(): ?string
    {
        return route('community.admin.members.index');
    }
}
