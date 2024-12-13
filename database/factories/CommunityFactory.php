<?php

namespace jfsullivan\CommunityManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jfsullivan\CommunityManager\Models\Community;

class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition()
    {
        $userClass = config('community-manager.user_model');

        return [
            'name' => $this->faker->company(),
            'user_id' => $userClass::factory(),
            'join_id' => $this->faker->randomNumber(6),
            'password' => bin2hex(random_bytes(6)),
            'track_member_balances' => $this->faker->boolean(50),
        ];
    }
}
