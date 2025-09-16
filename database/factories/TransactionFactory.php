<?php

namespace jfsullivan\CommunityManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;

class TransactionFactory extends Factory
{
    /**
     * Get the model class from config
     */
    public function modelName()
    {
        return config('community-manager.transaction_model', Transaction::class);
    }

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
