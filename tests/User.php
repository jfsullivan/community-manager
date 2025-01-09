<?php

namespace jfsullivan\CommunityManager\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jfsullivan\CommunityManager\Database\Factories\UserFactory;
use jfsullivan\CommunityManager\Traits\CanBeSiteAdmin;
use jfsullivan\CommunityManager\Traits\HasCommunities;

class User extends Authenticatable
{
    use CanBeSiteAdmin;
    use HasCommunities;
    use HasFactory;
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
