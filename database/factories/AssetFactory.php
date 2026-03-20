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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'ticker' => $this->faker->regexify('[A-Z]{4}[1-4]{2}'),
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement(['stock', 'fii']),
        ];
    }
}
