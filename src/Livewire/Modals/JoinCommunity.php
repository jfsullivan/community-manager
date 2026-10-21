<?php

namespace jfsullivan\CommunityManager\Livewire\Modals;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use jfsullivan\ApexUi\Modal\FormModalComponent;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\MemberManager\Enums\JoinMode;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\MemberManager\Models\Type;
use Livewire\Attributes\Rule;
use Livewire\Livewire;

/**
 * Join a community with its ID and password, as its join mode allows:
 * open (`password`) admits at once, `approval` creates a pending membership
 * an admin confirms, `invite_only` refuses. Closed communities
 * ({@see Community::joinClosedReason()}) refuse too.
 */
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

        // Closed to new members right now (e.g. disabled), or invite only.
        if (($closed = $community->joinClosedReason()) !== null) {
            $this->addError('form', $closed);

            return;
        }

        if ($community->joinMode() === JoinMode::InviteOnly) {
            $this->addError('form', 'This community is invite only. Ask an admin to send you an invitation.');

            return;
        }

        // A ban blocks rejoining until an admin lifts it.
        if ($community->hasBannedMember(Auth::user()->id)) {
            $this->addError('form', 'You are unable to join that community.');

            return;
        }

        if ($community->joinMode() === JoinMode::Password) {
            $this->admitNow($community);

            return;
        }

        $existing = $community->members()->where('user_id', Auth::user()->id)->first();

        // A pending row (no start, not ended) is an earlier request still
        // awaiting confirmation. A rejected one (ended) may ask again below.
        if ($existing && is_null($existing->membership->start_at) && is_null($existing->membership->end_at)) {
            $this->addError('form', 'Your request to join that community is pending an admin\'s confirmation.');

            return;
        }

        if ($existing && $existing->membership->start_at && (is_null($existing->membership->end_at) || Carbon::parse($existing->membership->end_at)->isFuture())) {
            $this->addError('form', 'You are already a member of that community.');

            return;
        }

        if ($existing) {
            // Former member (or a turned-down request) asking again: reactivate
            // the row as PENDING (approval-mode joins await an admin's
            // confirmation). The status log keeps the full timeline.
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

    /**
     * Open joining: make the user a current member straight away — a new
     * membership, an earlier pending or turned-down request, or a former
     * member rejoining — then reload the page they joined from so the
     * community shows up in its lists and menus.
     */
    protected function admitNow($community): void
    {
        $userId = Auth::user()->id;
        $membership = $community->memberships()->where('user_id', $userId)->first();

        if ($membership && $membership->start_at && (is_null($membership->end_at) || Carbon::parse($membership->end_at)->isFuture())) {
            $this->addError('form', 'You are already a member of that community.');

            return;
        }

        if ($membership) {
            $membership->update(['start_at' => now(), 'end_at' => null]);
        } else {
            $community->members()->attach($userId, [
                'role_id' => Role::where('slug', 'member')->value('id'),
                'type_id' => Type::where('slug', 'standard')->value('id'),
                'start_at' => now(),
                'created_at' => now(),
            ]);

            $membership = $community->memberships()->where('user_id', $userId)->first();
        }

        $membership?->setStatus('joined', 'open-self-join');

        session()->flash('notify', [
            'title' => 'Welcome',
            'type' => 'success',
            'message' => "You're now a member of {$community->name}.",
        ]);

        $this->redirect(Livewire::originalUrl());
    }

    public function render()
    {
        return view('community-manager::livewire.join-community');
    }
}
