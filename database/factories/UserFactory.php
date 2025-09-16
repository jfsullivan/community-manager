<?php

namespace jfsullivan\CommunityManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use jfsullivan\CommunityManager\Models\User;

class UserFactory extends Factory
{
    /**
     * Get the model class from config
     */
    public function modelName()
    {
        return config('community-manager.user_model', User::class);
    }

    public function definition()
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
