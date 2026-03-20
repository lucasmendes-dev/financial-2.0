<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 1000);
        $price = $this->faker->randomFloat(6, 1, 200);

        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'type' => $this->faker->randomElement(['buy', 'sell']),
            'quantity' => $quantity,
            'price_per_asset' => $price,
            'total' => $quantity * $price,
            'executed_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
