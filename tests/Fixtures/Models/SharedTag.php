<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Traits\BelongsToCommunity;

/** Rows with no community are system rows shared with every community. */
class SharedTag extends Model
{
    use BelongsToCommunity;

    protected bool $sharesCommunityRows = true;

    protected $table = 'shared_tags';

    protected $guarded = [];
}
