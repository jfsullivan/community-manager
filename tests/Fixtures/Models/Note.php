<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Traits\BelongsToCommunity;

/** A community-owned row with the default strict isolation. */
class Note extends Model
{
    use BelongsToCommunity;

    protected $table = 'notes';

    protected $guarded = [];
}
