<?php

namespace jfsullivan\CommunityManager\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-community views, for Context\Tasks\RegisterCommunityViewPaths
 * (config('community-manager.context.view_paths')): e.g. a community's
 * template folder first, then the default template.
 */
interface ProvidesCommunityViewPaths
{
    /**
     * View directories searched before the app's own, most specific first.
     *
     * @return array<int, string>
     */
    public function viewPaths(Model $community): array;

    /**
     * Anonymous Blade component directories, most specific first, keyed by
     * path with the component prefix as value (null for none).
     *
     * @return array<string, string|null>
     */
    public function componentPaths(Model $community): array;
}
