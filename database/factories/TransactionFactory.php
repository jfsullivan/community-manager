<?php

namespace jfsullivan\CommunityManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userClass = config('community-manager.user_model', 'jfsullivan\\CommunityManager\\Tests\\User');

        return [
            'transacted_at' => now(),
            'community_id' => Community::factory(),
            'type_id' => TransactionType::factory(),
            'user_id' => $userClass::factory(),
            'amount' => -500,
            'description' => $this->faker->sentence(),
        ];
    }
}
