<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $assetIds = Asset::pluck('id')->toArray();

        // Collect unique pairs to satisfy the composite unique index
        $positions = [];
        while (count($positions) < 3500) {
            $u = $userIds[array_rand($userIds)];
            $a = $assetIds[array_rand($assetIds)];
            
            $key = $u . '_' . $a;
            if (!isset($positions[$key])) {
                $positions[$key] = [
                    'user_id' => $u,
                    'asset_id' => $a
                ];
            }
        }

        // Create positions in chunks using the factory to generate random quantities and prices
        foreach (array_chunk(array_values($positions), 100) as $chunk) {
            foreach ($chunk as $pos) {
                Position::factory()->create([
                    'user_id' => $pos['user_id'],
                    'asset_id' => $pos['asset_id'],
                ]);
            }
        }
    }
}
