<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected static array $tickers = [];

    public function definition(): array
    {
        if (empty(self::$tickers)) {
            self::$tickers = require database_path('data/assets_ticker.php');
        }

        return [
            'id' => Str::uuid(),
            'ticker' => $this->faker->unique()->randomElement(self::$tickers),
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement(['stock', 'fii']),
        ];
    }
}
