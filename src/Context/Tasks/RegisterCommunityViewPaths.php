<?php

namespace jfsullivan\CommunityManager\Context\Tasks;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use jfsullivan\CommunityManager\Contracts\ProvidesCommunityViewPaths;
use jfsullivan\CommunityManager\Contracts\SwitchesCommunity;
use ReflectionProperty;

/**
 * Puts the current community's view and anonymous-component directories
 * (from the context.view_paths provider) in front of the app's own, and
 * restores the original lists when forgotten, so one community's template
 * never answers for the next in a worker, Octane or a multi-community test.
 */
class RegisterCommunityViewPaths implements SwitchesCommunity
{
    /** @var array<int, string>|null */
    protected ?array $baselineViewPaths = null;

    /** @var array<int, array{path: string, prefix: string|null, prefixHash: string}>|null */
    protected ?array $baselineComponentPaths = null;

    public function __construct(protected Application $app) {}

    public function makeCurrent(Model $community): void
    {
        $provider = $this->provider();

        if ($provider === null) {
            return;
        }

        $finder = View::getFinder();
        $blade = $this->app->make('blade.compiler');

        if (! $finder instanceof FileViewFinder) {
            return;
        }

        $this->baselineViewPaths ??= $finder->getPaths();
        $this->baselineComponentPaths ??= $blade->getAnonymousComponentPaths();

        $finder->setPaths(array_values(array_unique(array_merge($provider->viewPaths($community), $this->baselineViewPaths))));
        $finder->flush();

        $this->setComponentPaths($blade, []);

        foreach ($provider->componentPaths($community) as $path => $prefix) {
            $blade->anonymousComponentPath($path, $prefix);
        }

        $this->setComponentPaths($blade, array_merge($blade->getAnonymousComponentPaths(), $this->baselineComponentPaths));
    }

    public function forgetCurrent(): void
    {
        if ($this->baselineViewPaths === null) {
            return;
        }

        $finder = View::getFinder();

        if ($finder instanceof FileViewFinder) {
            $finder->setPaths($this->baselineViewPaths);
            $finder->flush();
        }

        $this->setComponentPaths($this->app->make('blade.compiler'), $this->baselineComponentPaths ?? []);

        $this->baselineViewPaths = null;
        $this->baselineComponentPaths = null;
    }

    protected function provider(): ?ProvidesCommunityViewPaths
    {
        $class = config('community-manager.context.view_paths');

        return $class ? $this->app->make($class) : null;
    }

    /** BladeCompiler can add anonymous component paths but not remove them. */
    protected function setComponentPaths(BladeCompiler $blade, array $paths): void
    {
        (new ReflectionProperty($blade, 'anonymousComponentPaths'))->setValue($blade, $paths);
    }
}
