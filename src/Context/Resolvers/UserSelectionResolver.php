<?php

namespace jfsullivan\CommunityManager\Context\Resolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use jfsullivan\CommunityManager\Contracts\ChecksMembership;
use jfsullivan\CommunityManager\Contracts\CommunityResolver;

/**
 * The signed-in user's community: the one they selected
 * (users.current_community_id, switched by PUT /current-community), or in
 * scoped_users mode the one their login belongs to (users.community_id).
 *
 * With context.verify_membership on (the default), a community the user
 * doesn't belong to resolves to none unless they pass the site-admin ability.
 */
class UserSelectionResolver implements CommunityResolver
{
    public function __construct(protected ChecksMembership $membership) {}

    public function resolve(Request $request): ?Model
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $column = config('community-manager.membership.mode', 'memberships') === 'scoped_users'
            ? config('community-manager.membership.scoped_users.column', 'community_id')
            : 'current_community_id';

        $communityId = $user->getAttribute($column);

        if ($communityId === null) {
            return null;
        }

        $communityClass = config('community-manager.community_model');
        $community = $communityClass::query()->find($communityId);

        if (! $community) {
            return null;
        }

        if (config('community-manager.context.verify_membership', true)
            && ! $this->membership->isMember($user, $community)
            && ! Gate::forUser($user)->allows(config('community-manager.site_admin_ability', 'manage-site'))) {
            return null;
        }

        return $community;
    }
}
