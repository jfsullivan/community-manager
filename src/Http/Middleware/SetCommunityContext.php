<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Context\CommunityContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias `community.context`: resolves the request's community with the
 * configured resolvers and makes it current explicitly (running the
 * switching tasks) for the rest of the request, then restores what was
 * current before. Place it after middleware that changes the user's
 * selected community (e.g. an admin "view as").
 */
class SetCommunityContext
{
    public function __construct(protected CommunityContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $snapshot = $this->context->snapshot();

        $this->context->set($this->context->resolve($request));

        try {
            return $next($request);
        } finally {
            $this->context->restore($snapshot);
        }
    }
}
