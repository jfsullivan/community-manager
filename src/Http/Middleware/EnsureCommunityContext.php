<?php

namespace jfsullivan\CommunityManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Context\CommunityContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias `community.required`: a 404 when no community is current, e.g. a
 * community-site route reached on an unknown host.
 */
class EnsureCommunityContext
{
    public function __construct(protected CommunityContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->context->has()) {
            abort(404);
        }

        return $next($request);
    }
}
