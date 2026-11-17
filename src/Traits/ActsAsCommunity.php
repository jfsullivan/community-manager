<?php

namespace jfsullivan\CommunityManager\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use jfsullivan\CommunityManager\Context\CommunityContext;
use jfsullivan\CommunityManager\Contracts\ChecksMembership;

/**
 * The community core's side of a community model: the current community
 * ({@see CommunityContext}) and membership answers in either membership
 * mode ({@see ChecksMembership}).
 *
 * The package's Community model uses it. An app whose community model
 * doesn't extend that model (e.g. one in membership mode 'scoped_users'
 * without member-manager) uses it directly and points
 * config('community-manager.community_model') at its model.
 *
 * Signatures match spatie/laravel-multitenancy's IsTenant, so the model can
 * also be a Spatie tenant.
 */
trait ActsAsCommunity
{
    /** The current community, or null. */
    public static function current(): ?static
    {
        $community = app(CommunityContext::class)->current();

        return $community instanceof static ? $community : null;
    }

    /** Whether this is the current community. */
    public function isCurrent(): bool
    {
        $current = app(CommunityContext::class)->current();

        return $current !== null && $current->getKey() == $this->getKey();
    }

    /** Whether the user belongs to this community, in either membership mode ({@see ChecksMembership}). */
    public function includesUser(?Authenticatable $user): bool
    {
        return app(ChecksMembership::class)->isMember($user, $this);
    }

    /** The user's role slug here ('owner' for the owner), or null when they aren't a member. */
    public function roleFor(?Authenticatable $user): ?string
    {
        return app(ChecksMembership::class)->roleOf($user, $this);
    }
}
