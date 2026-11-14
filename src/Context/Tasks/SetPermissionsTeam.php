<?php

namespace jfsullivan\CommunityManager\Context\Tasks;

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Contracts\SwitchesCommunity;

/**
 * Points spatie/laravel-permission's teams at the current community
 * (setPermissionsTeamId), and back to the earlier team when forgotten.
 * Does nothing when that package isn't installed.
 */
class SetPermissionsTeam implements SwitchesCommunity
{
    protected bool $captured = false;

    protected int|string|null $previous = null;

    public function makeCurrent(Model $community): void
    {
        if (! function_exists('setPermissionsTeamId')) {
            return;
        }

        if (! $this->captured) {
            $this->previous = function_exists('getPermissionsTeamId') ? getPermissionsTeamId() : null;
            $this->captured = true;
        }

        setPermissionsTeamId($community->getKey());
    }

    public function forgetCurrent(): void
    {
        if (! $this->captured || ! function_exists('setPermissionsTeamId')) {
            return;
        }

        setPermissionsTeamId($this->previous);
        $this->captured = false;
        $this->previous = null;
    }
}
