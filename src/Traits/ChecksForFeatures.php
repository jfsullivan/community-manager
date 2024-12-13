<?php

namespace jfsullivan\CommunityManager\Traits;

trait ChecksForFeatures
{
    public function trackMemberBalances()
    {
        return config('community-manager.features.manage-members')
                && config('community-manager.features.track_member_balances');
    }
}
