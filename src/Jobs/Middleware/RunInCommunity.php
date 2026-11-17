<?php

namespace jfsullivan\CommunityManager\Jobs\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Context\CommunityContext;

/**
 * Job middleware: run the job inside a named community, whatever was current
 * when it was dispatched. `public function middleware(): array { return [new RunInCommunity($this->community)]; }`
 */
class RunInCommunity
{
    public int|string $communityId;

    public function __construct(Model|int|string $community)
    {
        $this->communityId = $community instanceof Model ? $community->getKey() : $community;
    }

    public function handle(object $job, Closure $next): mixed
    {
        return app(CommunityContext::class)->run($this->communityId, fn () => $next($job));
    }
}
