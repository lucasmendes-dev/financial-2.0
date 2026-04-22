<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\MarketData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MarketData>
 */
class MarketDataFactory extends Factory
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
            'asset_id' => Asset::factory(),
            'regular_market_price' => $this->faker->randomFloat(6, 1, 200),
            'regular_market_change' => $this->faker->randomFloat(6, 1, 200),
            'regular_market_change_percent' => $this->faker->randomFloat(6, 1, 200),
            'logo_url' => $this->faker->url(),
            'fetched_at' => $this->faker->dateTime(),
        ];
    }
}
