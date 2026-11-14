<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/** No community column: the arch helper must leave it alone. */
class Setting extends Model
{
    protected $table = 'settings';

    protected $guarded = [];
}
