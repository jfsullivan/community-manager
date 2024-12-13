<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Modals;

use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Memberships\Forms\AddMemberForm;
use jfsullivan\MemberManager\Actions\CreateInvitation;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;
use Spatie\LaravelOptions\Options;
use jfsullivan\MemberManager\Livewire\Modals\AddMemberModal as AddMemberModalComponent;

class AddMemberModal extends AddMemberModalComponent
{

    public function owningModel(): mixed
    {
        $communityModel = config('community-manager.community_model');

        return $communityModel::find($this->owning_model_id);
    }
}
