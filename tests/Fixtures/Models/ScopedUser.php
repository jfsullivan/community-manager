<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use jfsullivan\CommunityManager\Traits\BelongsToCommunity;
use jfsullivan\CommunityManager\Traits\BelongsToOneCommunity;

/** A login in membership mode 'scoped_users': one community per login, null = platform staff. */
class ScopedUser extends Authenticatable
{
    use BelongsToCommunity;
    use BelongsToOneCommunity;

    protected string $whenNoCommunity = 'unowned';

    protected $table = 'users';

    protected $guarded = [];
}
