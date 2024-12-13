<?php

namespace jfsullivan\BrainTools\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use jfsullivan\BrainTools\Models\Organization;
use jfsullivan\BrainTools\Models\Transaction;
use jfsullivan\BrainTools\Models\TransactionType;

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
        return [
            'transacted_at' => now(),
            'organization_id' => Organization::factory(),
            'type_id' => TransactionType::factory(),
            'amount' => -500
        ];
    }
}
