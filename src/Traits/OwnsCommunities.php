<?php

namespace jfsullivan\CommunityManager\Traits;

trait OwnsCommunities
{
    /**************************************************************************
     * Model Relationships
    ***************************************************************************/
    public function ownedCommunities()
    {
        return $this->hasMany(config('community-manager.community_model'));
    }

    public function ownsCommunity($community)
    {
        return $this->id == $community->user_id;
    }
}
