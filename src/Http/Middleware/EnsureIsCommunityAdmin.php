<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsCommunityAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->user()->hasCurrentCommunity() 
            && ($request->user()->id != $request->user()->currentCommunity->user_id
                    && !$request->user()->currentCommunity->memberships()->whereRelation('role', 'slug', 'admin')->where('user_id', $request->user()->id)->exists()
                )
         ) {
            abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
