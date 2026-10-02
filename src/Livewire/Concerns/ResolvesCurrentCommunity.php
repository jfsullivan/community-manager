<?php

namespace jfsullivan\CommunityManager\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * The signed-in user's current community, loaded by its id. The package
 * doesn't know the host app's User model (or its currentCommunity relation),
 * so it reads the column and resolves the configured community model.
 */
trait ResolvesCurrentCommunity
{
    protected function currentCommunity(): mixed
    {
        $communityId = Auth::user()?->getAttribute('current_community_id');

        return $communityId ? app(config('community-manager.community_model'))::find($communityId) : null;
    }
}
