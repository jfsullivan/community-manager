<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Pages;

use Illuminate\Support\Facades\Auth;
use jfsullivan\MemberManager\Livewire\Pages\MemberManagementPage as MemberManagementPageComponent;
use Livewire\Attributes\Computed;

class MemberManagementPage extends MemberManagementPageComponent
{
    public $community_id;

    public function mount()
    {
        parent::mount();
    }

    public function owningModel(): mixed
    {
        return $this->community;
    }

    public function layout(): string
    {
        return config('community-manager.admin_layout');
    }

    public function addMemberModal(): string
    {
        return 'community-manager::memberships.modals.add-member-modal';
    }

    public function importMembersModal(): string
    {
        return 'community-manager::memberships.modals.import-members-modal';
    }

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    public function render()
    {
        return view('member-manager::livewire.pages.member-management-page')
            ->layout('components.layouts.app');
    }
}
