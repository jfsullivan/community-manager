<?php

namespace jfsullivan\CommunityManager\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jfsullivan\CommunityManager\Database\Factories\UserFactory;
use jfsullivan\CommunityManager\Traits\HasCommunityMemberships;
use jfsullivan\CommunityManager\Traits\OwnsCommunities;
use jfsullivan\MemberManager\Traits\HasFullName;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasCommunityMemberships;
    use OwnsCommunities;
    use HasFullName;

    protected $fillable = ['name', 'email', 'current_community_id', 'first_name', 'last_name'];

    public $timestamps = false;

    protected $table = 'users';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
