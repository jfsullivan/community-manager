<?php

namespace jfsullivan\CommunityManager\Events;

use Illuminate\Foundation\Events\Dispatchable;
use jfsullivan\CommunityManager\Models\Community;

/** A site admin enabled a disabled community again. */
class CommunityEnabled
{
    use Dispatchable;

    public function __construct(public Community $community) {}
}
