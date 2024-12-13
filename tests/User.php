<?php

namespace jfsullivan\CommunityManager\Tests;

use jfsullivan\CommunityManager\Database\Factories\UserFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use jfsullivan\CommunityManager\Traits\HasCommunities;
use jfsullivan\CommunityManager\Traits\CanBeSiteAdmin;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use HasCommunities;
    use CanBeSiteAdmin;
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
