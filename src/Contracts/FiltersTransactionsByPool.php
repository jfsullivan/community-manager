<?php

namespace jfsullivan\CommunityManager\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * How a host app ties transactions to its pools, for the Pool filter on a
 * member's transaction history. The package doesn't know what a pool is or
 * how a transaction's `model` leads to one, so the app supplies this through
 * config('community-manager.transaction_pool_filter'). Without it, the page
 * has no Pool filter.
 */
interface FiltersTransactionsByPool
{
    /**
     * Pools the member's transactions in this community belong to, newest
     * first. `detail` (e.g. the season) tells apart pools that share a name.
     *
     * @return array<int|string, array{name: string, detail: string|null}> pool id => option
     */
    public function poolOptions(Model $community, Authenticatable $user): array;

    /** Narrow a transactions query to one pool. */
    public function applyPoolFilter(Builder $query, int|string $poolId): Builder;
}
