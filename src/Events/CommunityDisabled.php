<?php

namespace jfsullivan\CommunityManager\Events;

use Illuminate\Foundation\Events\Dispatchable;
use jfsullivan\CommunityManager\Models\Community;

/** A site admin disabled the community; apps hook their own side effects here. */
class CommunityDisabled
{
    use Dispatchable;

    public function __construct(public Community $community) {}
}
