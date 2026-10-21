<?php

namespace jfsullivan\CommunityManager\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use jfsullivan\CommunityManager\Models\Community;

/**
 * Low-balance reminder: when a member's balance in their current community is
 * below the community's `low_balance_threshold`, a popup asks them to add
 * funds and opens the Add Funds dialog. Shown at most once every
 * REMIND_EVERY_DAYS per member and community (a cache key marks it shown).
 *
 * Place `<x-community-manager::low-balance-reminder />` once in the app layout.
 */
class LowBalanceReminder extends Component
{
    public const REMIND_EVERY_DAYS = 7;

    public bool $show = false;

    public ?Community $community = null;

    public int $balanceCents = 0;

    public int $thresholdCents = 0;

    public function __construct()
    {
        $user = Auth::user();
        $community = $user?->currentCommunity;

        if (! $community instanceof Community
            || ! $community->tracksMemberAccounting()
            || $community->low_balance_threshold === null) {
            return;
        }

        $key = self::cacheKey($user->getAuthIdentifier(), $community);

        if (Cache::has($key)) {
            return;
        }

        $balanceCents = (int) $community->memberBalance($user);

        if ($balanceCents >= $community->low_balance_threshold) {
            return;
        }

        // add() is atomic: only the first request in the window shows it.
        if (! Cache::add($key, true, now()->addDays(self::REMIND_EVERY_DAYS))) {
            return;
        }

        $this->show = true;
        $this->community = $community;
        $this->balanceCents = $balanceCents;
        $this->thresholdCents = $community->low_balance_threshold;
    }

    public static function cacheKey(int|string $userId, Community $community): string
    {
        return 'low-balance-reminder:'.$userId.':'.$community->id;
    }

    public function render(): View
    {
        return view('community-manager::components.low-balance-reminder');
    }
}
