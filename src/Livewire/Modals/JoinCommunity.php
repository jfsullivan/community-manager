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

        // A ban blocks rejoining until an admin lifts it.
        if ($community->hasBannedMember(Auth::user()->id)) {
            $this->addError('form', 'You are unable to join that community.');

            return;
        }

        $existing = $community->members()->where('user_id', Auth::user()->id)->first();

        if ($existing && is_null($existing->membership->start_at)) {
            // A pending row means an earlier request is still awaiting admin
            // confirmation — say so rather than "already a member".
            $this->addError('form', 'Your request to join that community is pending an admin\'s confirmation.');

            return;
        }

        if ($existing && (is_null($existing->membership->end_at) || Carbon::parse($existing->membership->end_at)->isFuture())) {
            $this->addError('form', 'You are already a member of that community.');

            return;
        }

        if ($existing) {
            // Former member rejoining: reactivate the row as PENDING again
            // (community self-joins always await admin confirmation). The
            // status log keeps the full timeline.
            $membership = $community->memberships()->where('user_id', Auth::user()->id)->first();
            $membership?->update(['start_at' => null, 'end_at' => null]);
            $membership?->setStatus('rejoin-requested', 'pending-self-join');
        } else {
            $memberRoleId = Role::where('slug', 'member')->value('id');
            $memberTypeId = Type::where('slug', 'standard')->value('id');

            // Self-joins are PENDING: attach with no start_at so the membership
            // does not grant access until an admin confirms it.
            $community->members()->attach(Auth::user()->id, [
                'role_id' => $memberRoleId,
                'type_id' => $memberTypeId,
                'start_at' => null,
                'created_at' => Carbon::now(),
            ]);

            $community->memberships()
                ->where('user_id', Auth::user()->id)
                ->first()
                ?->setStatus('joined', 'pending-self-join');
        }

        $community->load('members');

        if ($community->members()->where('user_id', Auth::user()->id)->exists()) {
            $this->dispatch('refresh-community-list');

            $this->dispatch('notify',
                title: 'Request Sent',
                type: 'success',
                message: 'Your request to join has been sent. You\'ll get access once an admin confirms it.',
            );
        }

        $this->closeModal();
    }

    public function render()
    {
        return view('community-manager::livewire.join-community');
    }
}
