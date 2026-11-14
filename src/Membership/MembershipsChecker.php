<?php

namespace jfsullivan\CommunityManager\Membership;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Contracts\ChecksMembership;

/**
 * Membership mode 'memberships' (the default): global users who belong to
 * communities through member-manager memberships. A member is the owner or
 * holds a current (started, not ended) membership; the role is the
 * membership role's slug.
 */
class MembershipsChecker implements ChecksMembership
{
    public function isMember(?Authenticatable $user, Model $community): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->isOwner($user, $community) || $this->currentMembership($user, $community) !== null;
    }

    public function roleOf(?Authenticatable $user, Model $community): ?string
    {
        if ($user === null) {
            return null;
        }

        if ($this->isOwner($user, $community)) {
            return 'owner';
        }

        $membership = $this->currentMembership($user, $community);

        if ($membership === null) {
            return null;
        }

        return data_get($membership, 'role.slug') ?? 'member';
    }

    public function isAdmin(?Authenticatable $user, Model $community): bool
    {
        if ($user === null) {
            return false;
        }

        if (method_exists($community, 'isCommunityAdmin')) {
            return (bool) $community->isCommunityAdmin($user->getAuthIdentifier());
        }

        return in_array($this->roleOf($user, $community), ['owner', 'admin'], true);
    }

    protected function isOwner(Authenticatable $user, Model $community): bool
    {
        $ownerId = $community->getAttribute('user_id');

        return $ownerId !== null && $ownerId == $user->getAuthIdentifier();
    }

    protected function currentMembership(Authenticatable $user, Model $community): ?Model
    {
        $membershipClass = config('member-manager.membership_model');

        if (! $membershipClass || ! class_exists($membershipClass)) {
            return null;
        }

        return $membershipClass::query()
            ->where('model_type', $community->getMorphClass())
            ->where('model_id', $community->getKey())
            ->where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('start_at')
            ->where('start_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>', now()))
            ->with('role')
            ->first();
    }
}
