<?php

namespace Tests\Feature\Services;

use App\Models\Asset;
use App\Models\Position;
use App\Models\Transaction;
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
            'avg_price' => '50.00',
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
            'avg_price' => '60.00',
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
            'avg_price' => '50.00',
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
            'avg_price' => '20.00',
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

        $this->assertDatabaseMissing('positions', [
            'id' => $position->id,
        ]);
    }

    public function test_recalculate_position_from_multiple_buys(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'buy',
            'quantity' => 10,
            'price_per_asset' => 30.0,
            'total' => 300.0,
            'executed_at' => '2024-01-01 10:00:00',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'buy',
            'quantity' => 10,
            'price_per_asset' => 30.50,
            'total' => 305.0,
            'executed_at' => '2024-01-02 10:00:00',
        ]);

        $this->service->recalculatePosition($user->id, $asset->id);

        // Total = 300 + 305 = 605, Qty = 20, Avg = 605/20 = 30.25
        $this->assertDatabaseHas('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 20,
            'avg_price' => '30.25',
        ]);
    }

    public function test_recalculate_position_with_buys_and_sells(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'buy',
            'quantity' => 10,
            'price_per_asset' => 50.0,
            'total' => 500.0,
            'executed_at' => '2024-01-01 10:00:00',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'sell',
            'quantity' => 4,
            'price_per_asset' => 60.0,
            'total' => 240.0,
            'executed_at' => '2024-01-02 10:00:00',
        ]);

        $this->service->recalculatePosition($user->id, $asset->id);

        // Buy 10@50 → qty=10, avg=50. Sell 4 → qty=6, avg=50 (unchanged)
        $this->assertDatabaseHas('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 6,
            'avg_price' => '50.00',
        ]);
    }

    public function test_recalculate_position_deletes_when_all_sold(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        // Create a stale position that should be cleaned up
        Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 999,
            'avg_price' => 999,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'buy',
            'quantity' => 10,
            'price_per_asset' => 50.0,
            'total' => 500.0,
            'executed_at' => '2024-01-01 10:00:00',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'sell',
            'quantity' => 10,
            'price_per_asset' => 60.0,
            'total' => 600.0,
            'executed_at' => '2024-01-02 10:00:00',
        ]);

        $this->service->recalculatePosition($user->id, $asset->id);

        $this->assertDatabaseMissing('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
        ]);
    }

    public function test_recalculate_position_creates_position_if_not_exists(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'type' => 'buy',
            'quantity' => 10,
            'price_per_asset' => 25.0,
            'total' => 250.0,
            'executed_at' => '2024-01-01 10:00:00',
        ]);

        $this->assertDatabaseMissing('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
        ]);

        $this->service->recalculatePosition($user->id, $asset->id);

        $this->assertDatabaseHas('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => '25.00',
        ]);
    }

    public function test_recalculate_with_no_transactions_deletes_position(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => 50.0,
        ]);

        $this->service->recalculatePosition($user->id, $asset->id);

        $this->assertDatabaseMissing('positions', [
            'user_id' => $user->id,
            'asset_id' => $asset->id,
        ]);
    }
}

