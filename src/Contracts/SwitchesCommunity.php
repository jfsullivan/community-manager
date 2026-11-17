<?php

namespace jfsullivan\CommunityManager\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * A switching task: process state that follows the current community
 * (view shares, the permission team, view paths). Listed in
 * config('community-manager.context.tasks').
 *
 * makeCurrent() runs whenever a community becomes current; forgetCurrent()
 * runs before switching away (or to none) and must restore the state the
 * process had before, so nothing from one community leaks into the next.
 */
interface SwitchesCommunity
{
    public function makeCurrent(Model $community): void;

    public function forgetCurrent(): void;
}
