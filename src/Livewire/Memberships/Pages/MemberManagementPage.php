<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Pages;

use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;
use jfsullivan\MemberManager\Livewire\Pages\MemberManagementPage as BaseMemberManagementPage;

class MemberManagementPage extends BaseMemberManagementPage
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
        return 'community-manager::livewire.memberships.partials.breadcrumbs';
    }

    public function memberDetailsUrl($member): ?string
    {
        return route('community.admin.members.show', ['user_id' => $member->user_id]);
    }
}
