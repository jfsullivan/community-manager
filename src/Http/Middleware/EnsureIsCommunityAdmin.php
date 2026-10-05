<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsCommunityAdmin
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->hasCurrentCommunity()) {
            abort(403, 'No current community');
        }

        $currentCommunity = $request->user()->currentCommunity;

        if (! $currentCommunity) {
            abort(403, 'Current community not found');
        }

        // The owner, or a current (not pending, former or banned) admin member.
        if ($currentCommunity->isCommunityAdmin($request->user()->id)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action');
    }
}
