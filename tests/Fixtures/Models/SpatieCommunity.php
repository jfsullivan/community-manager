<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures\Models;

use jfsullivan\CommunityManager\Models\Community;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Models\Concerns\ImplementsTenant;

/** A community that is also a spatie/laravel-multitenancy tenant. */
class SpatieCommunity extends Community implements IsTenant
{
    use ImplementsTenant;
    use UsesMultitenancyConfig;

    protected $table = 'communities';

    public function getMorphClass()
    {
        return 'community';
    }
}
