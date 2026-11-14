<?php

namespace jfsullivan\CommunityManager\Isolation;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use jfsullivan\CommunityManager\Context\CommunityContext;

/**
 * The strict BelongsToCommunity scope.
 *
 * With a current community: that community's rows, plus rows with no
 * community only for a model that allows shared rows.
 * With none: what the model's whenNoCommunity() says: 'none' (nothing, the
 * default), 'unowned' (only rows with no community) or 'all'.
 *
 * Adds withoutCommunityScope() and forCommunity($community) to the builder.
 */
class CommunityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $column = $model->qualifyColumn(IsolationOptions::column($model));
        $communityId = app(CommunityContext::class)->id();

        if ($communityId === null) {
            match (IsolationOptions::whenNoCommunity($model)) {
                'all' => null,
                'unowned' => $builder->whereNull($column),
                default => $builder->whereRaw('1 = 0'),
            };

            return;
        }

        if (IsolationOptions::sharesRows($model)) {
            $builder->where(fn (Builder $query) => $query->where($column, $communityId)->orWhereNull($column));

            return;
        }

        $builder->where($column, $communityId);
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutCommunityScope', fn (Builder $builder) => $builder->withoutGlobalScope(self::class));

        $builder->macro('forCommunity', function (Builder $builder, mixed $community) {
            return $builder->withoutGlobalScope(self::class)->whereIn(
                $builder->getModel()->qualifyColumn(IsolationOptions::column($builder->getModel())),
                CommunityScope::keys($community),
            );
        });
    }

    /**
     * Community keys from a model, a key, or a list of either.
     *
     * @return array<int, mixed>
     */
    public static function keys(mixed $communities): array
    {
        $items = is_iterable($communities) ? $communities : [$communities];
        $keys = [];

        foreach ($items as $item) {
            $keys[] = $item instanceof Model ? $item->getKey() : $item;
        }

        return $keys;
    }
}
