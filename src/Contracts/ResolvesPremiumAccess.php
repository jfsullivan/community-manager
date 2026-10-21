<?php

namespace jfsullivan\CommunityManager\Contracts;

use jfsullivan\CommunityManager\Models\Community;

/**
 * Decides which paid features a community may use and how far its limits go,
 * for {@see Community::allowsFeature()} and {@see Community::featureLimit()}. Bound through
 * config('community-manager.premium.resolver'); the default allows every
 * feature with no limits, so an app without billing sees everything unlocked.
 */
interface ResolvesPremiumAccess
{
    /** Whether the community may use the feature (e.g. 'messages', 'premium'). */
    public function allows(Community $community, string $feature): bool;

    /** The community's limit for the key (e.g. 'members'), or null for no limit. */
    public function limit(Community $community, string $key): ?int;
}
