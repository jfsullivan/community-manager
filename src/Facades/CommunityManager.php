<?php

namespace jfsullivan\CommunityManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \jfsullivan\CommunityManager\CommunityManager
 */
class CommunityManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \jfsullivan\CommunityManager\CommunityManager::class;
    }
}
