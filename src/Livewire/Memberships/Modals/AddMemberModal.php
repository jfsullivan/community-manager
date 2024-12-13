<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Modals;

use jfsullivan\MemberManager\Livewire\Modals\AddMemberModal as AddMemberModalComponent;

class AddMemberModal extends AddMemberModalComponent
{
    public function owningModel(): mixed
    {
        $communityModel = config('community-manager.community_model');

        return $communityModel::find($this->owning_model_id);
    }
}
