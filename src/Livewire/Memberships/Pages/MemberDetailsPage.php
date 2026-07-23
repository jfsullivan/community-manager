<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Pages;

use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCommunity;
use jfsullivan\MemberManager\Livewire\Pages\MemberDetailsPage as BaseMemberDetailsPage;
use Livewire\Attributes\Computed;

class MemberDetailsPage extends BaseMemberDetailsPage
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
        return 'community-manager::livewire.memberships.partials.details-breadcrumbs';
    }

    public function activityView(): ?string
    {
        return 'community-manager::livewire.memberships.partials.member-activity';
    }

    public function membersIndexUrl(): string
    {
        return route('community.admin.members.index');
    }

    #[Computed]
    public function memberBalance()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::query()
            ->where('community_id', $this->community->id)
            ->where('user_id', $this->user_id)
            ->sum('amount');
    }

    #[Computed]
    public function recentTransactions()
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::query()
            ->with('type')
            ->where('community_id', $this->community->id)
            ->where('user_id', $this->user_id)
            ->orderByDesc('transacted_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }
}
