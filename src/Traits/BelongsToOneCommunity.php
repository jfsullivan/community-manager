<?php

namespace jfsullivan\CommunityManager\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * For the user model in membership mode 'scoped_users': each login belongs
 * to one community (membership.scoped_users.column); a null column marks
 * platform staff.
 *
 * Use it together with BelongsToCommunity and `protected string
 * $whenNoCommunity = 'unowned';` so a community only ever sees its own
 * logins, and central (no community) code only sees platform staff.
 */
trait BelongsToOneCommunity
{
    /** Platform staff: a login that belongs to no community. */
    public function isPlatformStaff(): bool
    {
        return $this->getAttribute($this->scopedCommunityColumn()) === null;
    }

    public function scopePlatformStaff(Builder $query): void
    {
        $query->whereNull($query->getModel()->qualifyColumn($this->scopedCommunityColumn()));
    }

    public function scopeCommunityLogins(Builder $query): void
    {
        $query->whereNotNull($query->getModel()->qualifyColumn($this->scopedCommunityColumn()));
    }

    protected function scopedCommunityColumn(): string
    {
        return config('community-manager.membership.scoped_users.column', 'community_id');
    }
}
