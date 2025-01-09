<?php

namespace jfsullivan\CommunityManager\Livewire\Articles\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\ArticleManager\Livewire\Pages\ArticleManagementPage as ArticleManagementPageComponent;
use Livewire\Attributes\Computed;

class ArticleManagementPage extends ArticleManagementPageComponent
{
    public $community_id;

    public function owningModel(): mixed
    {
        return $this->community;
    }

    public function layout(): string
    {
        return config('community-manager.admin_layout');
    }

    // public function addMemberModal(): string
    // {
    //     return 'community-manager::memberships.modals.add-member-modal';
    // }

    // public function importMembersModal(): string
    // {
    //     return 'community-manager::memberships.modals.import-members-modal';
    // }

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    // #[On('member-created')]
    // #[On('member-deleted')]
    // #[On('member-updated')]
    // public function render()
    // {
    //     return view('community-manager::livewire.memberships.pages.member-management-page')
    //         ->layout(config('community-manager.admin_layout'));
    // }
}
