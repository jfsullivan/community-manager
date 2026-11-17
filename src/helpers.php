<?php

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Context\CommunityContext;

if (! function_exists('current_community')) {
    /** The current community (community-manager's CommunityContext), or null. */
    function current_community(): ?Model
    {
        return app(CommunityContext::class)->current();
    }
}
