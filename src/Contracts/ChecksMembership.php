<?php

namespace jfsullivan\CommunityManager\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Answers membership questions the same way in either membership mode
 * (config('community-manager.membership.mode')): global users with
 * memberships, or community-scoped users.
 */
interface ChecksMembership
{
    /** Whether the user currently belongs to the community (owners included). */
    public function isMember(?Authenticatable $user, Model $community): bool;

    /** The user's role slug in the community ('owner' for its owner), or null when not a member. */
    public function roleOf(?Authenticatable $user, Model $community): ?string;

    /** Whether the user runs the community: its owner or an admin. */
    public function isAdmin(?Authenticatable $user, Model $community): bool;
}
