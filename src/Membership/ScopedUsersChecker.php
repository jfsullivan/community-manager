<?php

namespace jfsullivan\CommunityManager\Membership;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Contracts\ChecksMembership;

/**
 * Membership mode 'scoped_users': each login belongs to exactly one
 * community (membership.scoped_users.column, users.community_id; null means
 * platform staff, who are members of none). The role comes from
 * scoped_users.role_column when set ('member' otherwise); admins are the
 * owner and roles listed in scoped_users.admin_roles. Apps with richer
 * roles (e.g. spatie/laravel-permission teams) bind their own checker in
 * membership.checker.
 */
class ScopedUsersChecker implements ChecksMembership
{
    public function isMember(?Authenticatable $user, Model $community): bool
    {
        if (! $user instanceof Model) {
            return false;
        }

        $communityId = $user->getAttribute($this->column());

        return $communityId !== null && (string) $communityId === (string) $community->getKey();
    }

    public function roleOf(?Authenticatable $user, Model $community): ?string
    {
        if (! $this->isMember($user, $community)) {
            return null;
        }

        /** @var Model&Authenticatable $user */
        $ownerId = $community->getAttribute('user_id');

        if ($ownerId !== null && $ownerId == $user->getAuthIdentifier()) {
            return 'owner';
        }

        $roleColumn = config('community-manager.membership.scoped_users.role_column');

        return ($roleColumn ? $user->getAttribute($roleColumn) : null) ?? 'member';
    }

    public function isAdmin(?Authenticatable $user, Model $community): bool
    {
        $role = $this->roleOf($user, $community);

        return $role === 'owner'
            || in_array($role, (array) config('community-manager.membership.scoped_users.admin_roles', ['admin']), true);
    }

    protected function column(): string
    {
        return config('community-manager.membership.scoped_users.column', 'community_id');
    }
}
