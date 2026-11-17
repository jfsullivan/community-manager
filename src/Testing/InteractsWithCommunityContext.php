<?php

namespace jfsullivan\CommunityManager\Testing;

use Closure;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Context\CommunityContext;

/**
 * For test cases: `uses(InteractsWithCommunityContext::class)` (Pest) or
 * `use InteractsWithCommunityContext;` (PHPUnit). The context is forgotten
 * after each test.
 */
trait InteractsWithCommunityContext
{
    /** Make the community current for the rest of the test (null for explicitly none). */
    protected function setCurrentCommunity(Model|int|string|null $community): static
    {
        app(CommunityContext::class)->set($community);

        return $this;
    }

    protected function forgetCurrentCommunity(): static
    {
        app(CommunityContext::class)->forget();

        return $this;
    }

    /**
     * @template TReturn
     *
     * @param  Closure(?Model): TReturn  $callback
     * @return TReturn
     */
    protected function inCommunity(Model|int|string|null $community, Closure $callback): mixed
    {
        return app(CommunityContext::class)->run($community, $callback);
    }

    /** Called by Laravel's test case after each test. */
    protected function tearDownInteractsWithCommunityContext(): void
    {
        if (isset($this->app) && $this->app->bound(CommunityContext::class)) {
            $this->app->make(CommunityContext::class)->forget();
        }
    }
}
