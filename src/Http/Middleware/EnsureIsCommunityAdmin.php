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

        // Allow access if user is the community owner
        if ($request->user()->id == $currentCommunity->user_id) {
            return $next($request);
        }

        // Allow access if user is an admin member
        if ($currentCommunity->memberships()->whereRelation('role', 'slug', 'admin')->where('user_id', $request->user()->id)->exists()) {
            return $next($request);
        }

        abort(403, 'Unauthorized action');
    }
}
