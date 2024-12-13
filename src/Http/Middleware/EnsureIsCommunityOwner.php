<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Models\Community;

class EnsureIsCommunityOwner
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
        $community = Community::find(Auth::user()->currentCommunity->id);

        if (Auth::user()->id != $community->user_id) {
            abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
