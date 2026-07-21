<?php

namespace jfsullivan\CommunityManager\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * Community context for pages that act on a community: resolve it from the
 * community_id mount property (falling back to the user's current community)
 * and expose it as the page's owning model.
 */
trait ResolvesCommunity
{
    public $community_id;

    #[Computed]
    public function community()
    {
        $communityClass = app(config('community-manager.community_model'));

        return ($this->community_id)
            ? $communityClass::find($this->community_id)
            : Auth::user()->currentCommunity;
    }

    public function owningModel(): mixed
    {
        return $this->community;
    }
}
