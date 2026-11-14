<?php

namespace jfsullivan\CommunityManager\Isolation;

use Illuminate\Database\Eloquent\Model;

/**
 * A model's BelongsToCommunity options, with the package defaults for a
 * model that doesn't use the trait.
 */
class IsolationOptions
{
    public static function column(Model $model): string
    {
        return method_exists($model, 'getCommunityColumn')
            ? $model->getCommunityColumn()
            : config('community-manager.isolation.column', 'community_id');
    }

    public static function sharesRows(Model $model): bool
    {
        return method_exists($model, 'allowsSharedCommunityRows') && $model->allowsSharedCommunityRows();
    }

    /** 'none', 'unowned' or 'all'. */
    public static function whenNoCommunity(Model $model): string
    {
        return method_exists($model, 'whenNoCommunity')
            ? $model->whenNoCommunity()
            : config('community-manager.isolation.when_no_community', 'none');
    }
}
