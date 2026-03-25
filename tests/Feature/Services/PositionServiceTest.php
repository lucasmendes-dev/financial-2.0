<?php

namespace Tests\Feature\Services;

use App\Models\Asset;
use App\Models\Position;
use App\Models\User;
use App\Services\PositionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PositionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PositionService();
    }

    public function test_update_position_creates_new_position_if_not_exists(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        $data = [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'price_per_asset' => 50.0,
            'type' => 'buy'
        ];

        $this->service->updatePosition($data);

        $this->assertDatabaseHas('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => 50.0,
        ]);
    }

    public function test_update_position_updates_existing_position_on_buy(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => 50.0,
        ]);

        $data = [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 5,
            'price_per_asset' => 80.0,
            'total' => 400.0,
            'type' => 'buy'
        ];

        $this->service->updatePosition($data);

        // New total = (10 * 50) + 400 = 500 + 400 = 900
        // New quantity = 10 + 5 = 15
        // New avg price = 900 / 15 = 60.0

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'quantity' => 15,
            'avg_price' => 60.0,
        ]);
    }

    public function test_update_position_updates_existing_position_on_sell(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => 50.0,
        ]);

        $data = [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 4,
            'price_per_asset' => 100.0,
            'type' => 'sell'
        ];

        $this->service->updatePosition($data);

        // Quantity should decrease, avg price should remain same
        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'quantity' => 6,
            'avg_price' => 50.0,
        ]);
    }

    public function test_update_position_calculates_avg_price_correctly_on_multiple_buys(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        // 1st Buy
        $this->service->updatePosition([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'price_per_asset' => 10.0,
            'type' => 'buy'
        ]);

        // 2nd Buy
        $this->service->updatePosition([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 20,
            'price_per_asset' => 25.0,
            'total' => 500.0,
            'type' => 'buy'
        ]);

        // Total units = 30
        // Total cost = (10 * 10) + 500 = 100 + 500 = 600
        // Avg price = 600 / 30 = 20.0

        $this->assertDatabaseHas('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 30,
            'avg_price' => 20.0,
        ]);
    }

    public function test_update_position_handles_sell_all(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => 50.0,
        ]);

        $data = [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'price_per_asset' => 60.0,
            'type' => 'sell'
        ];

        $this->service->updatePosition($data);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'quantity' => 0,
            'avg_price' => 50.0,
        ]);
    }
}
