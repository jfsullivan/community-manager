<?php

namespace jfsullivan\CommunityManager\Context\Resolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Contracts\CommunityResolver;

/**
 * The community named by a route parameter (context.route_parameter,
 * 'community' by default): a bound community model, or a value matched
 * against context.route_columns in order. Resolving doesn't authorize: keep
 * the route's own membership middleware.
 */
class RouteParameterResolver implements CommunityResolver
{
    public function resolve(Request $request): ?Model
    {
        $route = $request->route();

        if (! is_object($route)) {
            return null;
        }

        $value = $route->parameter(config('community-manager.context.route_parameter', 'community'));
        $communityClass = config('community-manager.community_model');

        if ($value instanceof $communityClass) {
            return $value;
        }

        if (! is_scalar($value) || $value === '') {
            return null;
        }

        foreach ((array) config('community-manager.context.route_columns', ['id']) as $column) {
            if ($community = $communityClass::query()->where($column, $value)->first()) {
                return $community;
            }
        }

        return null;
    }
}
