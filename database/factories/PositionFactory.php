<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'quantity' => $this->faker->numberBetween(1, 5000),
            'avg_price' => $this->faker->randomFloat(6, 1, 200),
        ];
    }
}
