<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/** Has a community column but forgot BelongsToCommunity: the arch helper must catch it. */
class UnscopedEntry extends Model
{
    protected $table = 'unscoped_entries';

    protected $guarded = [];
}
