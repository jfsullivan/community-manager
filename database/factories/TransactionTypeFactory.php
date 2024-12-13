<?php

namespace jfsullivan\CommunityManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jfsullivan\CommunityManager\Models\TransactionType;

class TransactionTypeFactory extends Factory
{
    protected $model = TransactionType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'direction' => -1
        ];
    }
}
