<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Models\Community;
use Symfony\Component\HttpFoundation\Response;

/**
 * The member-accounting pages (the admin Transactions and Member Balances
 * pages, a member's transactions) don't exist for a community that doesn't
 * track member balances ({@see Community::tracksMemberAccounting()}):
 * they 404. Applied to those routes (alias `community-accounting`) and
 * registered as Livewire persistent middleware, so component actions on
 * these pages are checked again.
 */
class EnsureCommunityTracksMemberAccounting
{
    public function handle(Request $request, Closure $next): Response
    {
        $community = $request->user()?->currentCommunity;

        if ($community !== null && ! $community->tracksMemberAccounting()) {
            abort(404);
        }

        return $next($request);
    }
}
