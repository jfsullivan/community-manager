<?php

namespace jfsullivan\CommunityManager\Context;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use jfsullivan\CommunityManager\Contracts\CommunityResolver;
use jfsullivan\CommunityManager\Contracts\SwitchesCommunity;
use jfsullivan\CommunityManager\Exceptions\CommunityNotFoundException;

/**
 * The single "current community" for a request, queued job or command.
 *
 * A community is current either explicitly ({@see set()}, {@see run()}, the
 * community.context middleware, a community-aware job) or, during a request
 * with nothing set explicitly, lazily: the configured resolvers run on first
 * use and the answer is remembered on that request.
 *
 * Switching tasks (config('community-manager.context.tasks')) run only for
 * explicit switches, and are always undone before the next one.
 */
class CommunityContext
{
    public const REQUEST_ATTRIBUTE = 'community-manager.community';

    /** Whether a community (or explicitly none) was set, overriding lazy resolution. */
    protected bool $explicit = false;

    protected ?Model $community = null;

    protected bool $resolving = false;

    protected bool $switching = false;

    protected int $guardSuspensions = 0;

    /** @var array<int, SwitchesCommunity>|null */
    protected ?array $tasks = null;

    /** @var array<int, SwitchesCommunity> The tasks that ran makeCurrent() for the current community. */
    protected array $activeTasks = [];

    public function __construct(protected Application $app) {}

    /** The current community, or null. */
    public function current(): ?Model
    {
        if ($this->explicit) {
            return $this->community;
        }

        return $this->resolveLazily();
    }

    /** The current community's key, or null. */
    public function id(): int|string|null
    {
        return $this->current()?->getKey();
    }

    public function has(): bool
    {
        return $this->current() !== null;
    }

    /** Whether the community (or none) was set explicitly rather than resolved lazily. */
    public function isExplicit(): bool
    {
        return $this->explicit;
    }

    /** Whether switching tasks are running (bridges use it to ignore their own echo). */
    public function isSwitching(): bool
    {
        return $this->switching;
    }

    /**
     * Make the community current (a model, its key or slug), or null for
     * explicitly none. Undoes the previous community's switching tasks, then
     * runs them for the new one.
     */
    public function set(Model|int|string|null $community): void
    {
        $community = $this->find($community);

        if ($this->explicit && $this->sameCommunity($this->community, $community)) {
            $this->community = $community;

            return;
        }

        $this->switching = true;

        try {
            $this->forgetTasks();

            $this->explicit = true;
            $this->community = $community;

            if ($community !== null) {
                $this->makeTasks($community);
            }
        } finally {
            $this->switching = false;
        }
    }

    /** Drop any explicit community, undoing its switching tasks; lazy resolution applies again. */
    public function forget(): void
    {
        $this->switching = true;

        try {
            $this->forgetTasks();
        } finally {
            $this->switching = false;
        }

        $this->explicit = false;
        $this->community = null;
        $this->refresh();
    }

    /**
     * Run the callback with the community current (null for none), then put
     * back whatever was current before, even when it throws.
     *
     * @template TReturn
     *
     * @param  Closure(?Model): TReturn  $callback
     * @return TReturn
     */
    public function run(Model|int|string|null $community, Closure $callback): mixed
    {
        $snapshot = $this->snapshot();

        $this->set($community);

        try {
            return $callback($this->community);
        } finally {
            $this->restore($snapshot);
        }
    }

    /**
     * Run the callback with no current community.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function runWithoutCommunity(Closure $callback): mixed
    {
        return $this->run(null, fn () => $callback());
    }

    /**
     * The current state, for {@see restore()}.
     *
     * @return array{explicit: bool, community: ?Model}
     */
    public function snapshot(): array
    {
        return ['explicit' => $this->explicit, 'community' => $this->community];
    }

    /** @param  array{explicit: bool, community: ?Model}  $snapshot */
    public function restore(array $snapshot): void
    {
        if ($snapshot['explicit']) {
            $this->set($snapshot['community']);
        } else {
            $this->forget();
        }
    }

    /**
     * Run the configured resolvers against the request, in order; the first
     * community wins. It doesn't make the community current.
     */
    public function resolve(Request $request): ?Model
    {
        if ($this->resolving) {
            return null;
        }

        $this->resolving = true;

        try {
            foreach ((array) config('community-manager.context.resolvers', []) as $resolverClass) {
                $resolver = $this->app->make($resolverClass);

                if (! $resolver instanceof CommunityResolver) {
                    continue;
                }

                if ($community = $resolver->resolve($request)) {
                    return $community;
                }
            }

            return null;
        } finally {
            $this->resolving = false;
        }
    }

    /** Forget the lazily resolved community remembered on the current request (e.g. after login). */
    public function refresh(): void
    {
        if ($this->app->bound('request')) {
            $this->app->make('request')->attributes->remove(self::REQUEST_ATTRIBUTE);
        }
    }

    /**
     * Run the callback with the BelongsToCommunity write guard (and the
     * create-without-community check) off.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function withoutGuard(Closure $callback): mixed
    {
        $this->guardSuspensions++;

        try {
            return $callback();
        } finally {
            $this->guardSuspensions--;
        }
    }

    public function guardIsSuspended(): bool
    {
        return $this->guardSuspensions > 0;
    }

    /**
     * The community for a model, key or slug (null stays null).
     *
     * @throws CommunityNotFoundException
     */
    public function find(Model|int|string|null $community): ?Model
    {
        if ($community === null || $community instanceof Model) {
            return $community;
        }

        $class = $this->communityModel();
        $query = $class::query();
        $slugColumn = config('community-manager.hosts.slug_column');

        if (is_int($community) || ctype_digit($community)) {
            $found = $query->find($community);
        } else {
            $found = $slugColumn ? $query->where($slugColumn, $community)->first() : null;
        }

        if (! $found) {
            throw CommunityNotFoundException::for($community);
        }

        return $found;
    }

    /** @return class-string<Model> */
    public function communityModel(): string
    {
        return config('community-manager.community_model');
    }

    protected function resolveLazily(): ?Model
    {
        if ($this->resolving || ! config('community-manager.context.lazy', true) || ! $this->app->bound('request')) {
            return null;
        }

        /** @var Request $request */
        $request = $this->app->make('request');

        if ($request->attributes->has(self::REQUEST_ATTRIBUTE)) {
            return $request->attributes->get(self::REQUEST_ATTRIBUTE);
        }

        $community = $this->resolve($request);

        // Before routing, "none" may only mean the route parameter isn't
        // known yet, so it isn't remembered.
        if ($community !== null || $request->route() !== null) {
            $request->attributes->set(self::REQUEST_ATTRIBUTE, $community);
        }

        return $community;
    }

    protected function makeTasks(Model $community): void
    {
        foreach ($this->tasks() as $task) {
            $this->activeTasks[] = $task;
            $task->makeCurrent($community);
        }
    }

    protected function forgetTasks(): void
    {
        while ($task = array_pop($this->activeTasks)) {
            $task->forgetCurrent();
        }
    }

    /** @return array<int, SwitchesCommunity> */
    protected function tasks(): array
    {
        return $this->tasks ??= collect((array) config('community-manager.context.tasks', []))
            ->map(fn (string $class) => $this->app->make($class))
            ->filter(fn ($task) => $task instanceof SwitchesCommunity)
            ->values()
            ->all();
    }

    protected function sameCommunity(?Model $a, ?Model $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b;
        }

        return $a->getKey() == $b->getKey() && $a->getMorphClass() === $b->getMorphClass();
    }
}
