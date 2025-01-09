<?php

namespace jfsullivan\CommunityManager\Livewire\Memberships\Modals;

use jfsullivan\MemberManager\Livewire\Modals\ImportMembersModal as ImportMembersModalComponent;

class ImportMembersModal extends ImportMembersModalComponent
{
    public function owningModel(): mixed
    {
        $communityModel = config('community-manager.community_model');

        return $communityModel::find($this->owning_model_id);
    }
}
