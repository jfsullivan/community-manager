<?php

namespace jfsullivan\CommunityManager\Livewire\Modals;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Livewire\Attributes\Rule;

class JoinCommunity extends FormModalComponent
{
    public string $modalName = 'join-community';

    #[Rule('required|min:5', as: 'Community ID', onUpdate: false)]
    public $join_id = '';

    #[Rule('required|string', as: 'Community Password', onUpdate: false)]
    public $password = '';

    protected function initForm(): void
    {
        $this->reset('join_id', 'password');
    }

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function preventSubmit()
    {
        return Str::length($this->join_id) < 1 || Str::length($this->password) < 1;
    }

    public function save(): void
    {
        $this->validate();

        $communityModel = config('community-manager.community_model');

        $community = $communityModel::where('join_id', $this->join_id)->where('password', $this->password)->first();

        if (! $community) {
            $this->addError('form', 'Unable to find a community with that ID and Password combination.');

            return;
        }

        if ($community->members()->where('user_id', Auth::user()->id)->exists()) {
            $this->addError('form', 'You are already a member of that community.');

            return;
        }

        $memberRoleId = Role::where('slug', 'member')->value('id');
        $memberTypeId = Type::where('slug', 'standard')->value('id');

        $community->members()->attach(Auth::user()->id, [
            'role_id' => $memberRoleId,
            'type_id' => $memberTypeId,
            'created_at' => Carbon::now(),
        ]);

        $community->load('members');

        if ($community->members()->where('user_id', Auth::user()->id)->exists()) {
            $this->dispatch('refresh-community-list');

            $this->dispatch('notify',
                title: 'Successfully Joined Community',
                type: 'success',
                message: 'You can now access the community from your community list below.',
            );
        }

        $this->closeModal();
    }

    public function render()
    {
        return view('community-manager::livewire.join-community');
    }
}
