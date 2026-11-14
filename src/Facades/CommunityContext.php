<?php

namespace jfsullivan\CommunityManager\Facades;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use jfsullivan\CommunityManager\Context\CommunityContext as Context;

/**
 * @method static Model|null current()
 * @method static int|string|null id()
 * @method static bool has()
 * @method static bool isExplicit()
 * @method static void set(Model|int|string|null $community)
 * @method static void forget()
 * @method static mixed run(Model|int|string|null $community, Closure $callback)
 * @method static mixed runWithoutCommunity(Closure $callback)
 * @method static array{explicit: bool, community: Model|null} snapshot()
 * @method static void restore(array $snapshot)
 * @method static Model|null resolve(Request $request)
 * @method static void refresh()
 * @method static mixed withoutGuard(Closure $callback)
 * @method static Model|null find(Model|int|string|null $community)
 *
 * @see Context
 */
class CommunityContext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Context::class;
    }
}
