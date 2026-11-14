<?php

namespace jfsullivan\CommunityManager\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Finds the community a request is for. Listed in
 * config('community-manager.context.resolvers') and tried in order; the
 * first to return a community wins.
 */
interface CommunityResolver
{
    /** The request's community (an instance of the configured community model), or null. */
    public function resolve(Request $request): ?Model;
}
