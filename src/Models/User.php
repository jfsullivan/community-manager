<?php

namespace jfsullivan\CommunityManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jfsullivan\CommunityManager\Database\Factories\UserFactory;
use jfsullivan\CommunityManager\Traits\HasCommunityMemberships;
use jfsullivan\CommunityManager\Traits\OwnsCommunities;

class User extends Authenticatable
{
    use HasFactory;
    use OwnsCommunities;
    use HasCommunityMemberships;
    use Notifiable;

    protected $fillable = ['name', 'email'];

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
