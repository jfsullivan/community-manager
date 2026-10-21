<?php

namespace jfsullivan\CommunityManager\Support;

use jfsullivan\CommunityManager\Contracts\ResolvesPremiumAccess;
use jfsullivan\CommunityManager\Models\Community;

/** The default premium resolver: every feature allowed, no limits. */
class UnlimitedPremiumAccess implements ResolvesPremiumAccess
{
    public function allows(Community $community, string $feature): bool
    {
        return true;
    }

    public function limit(Community $community, string $key): ?int
    {
        return null;
    }
}
