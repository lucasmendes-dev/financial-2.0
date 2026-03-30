<?php

namespace Tests\Feature\Api\V1;

use App\Models\Asset;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_only_user_positions()
    {
        $otherUser = User::factory()->create();
        $assets = Asset::factory()->count(3)->create();

        foreach ($assets as $asset) {
            Position::factory()->create([
                'user_id' => $this->user->id,
                'asset_id' => $asset->id,
            ]);
        }

        $otherAsset = Asset::factory()->create();
        Position::factory()->count(1)->create([
            'user_id' => $otherUser->id,
            'asset_id' => $otherAsset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'asset_id', 'asset_ticker', 'quantity', 'avg_price']
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ]);
    }

    public function test_can_show_position()
    {
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/positions/{$position->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $position->id,
                    'asset_id' => $asset->id,
                    'asset_ticker' => $asset->ticker,
                    'quantity' => (float) $position->quantity,
                    'avg_price' => $position->avg_price->get(),
                ]
            ]);
    }

    public function test_cannot_show_others_position()
    {
        $otherUser = User::factory()->create();
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $otherUser->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/positions/{$position->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_can_delete_position()
    {
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/positions/{$position->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Deleted successfully']);

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    public function test_unauthenticated_user_cannot_access_positions()
    {
        $response = $this->getJson('/api/v1/positions');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_can_filter_positions_by_quantity_range()
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();

        Position::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset1->id, 'quantity' => 10]);
        Position::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset2->id, 'quantity' => 60]);
        Position::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset3->id, 'quantity' => 1100]);

        // Filter quantity > 50
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions?quantity_gt=50');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data');

        // Filter quantity < 1000
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions?quantity_lt=1000');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data');

        // Filter 50 < quantity < 1000
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions?quantity_gt=50&quantity_lt=1000');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity', 60);
    }

    public function test_can_filter_positions_by_avg_price_range()
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();

        Position::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset1->id, 'avg_price' => '5']);
        Position::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset2->id, 'avg_price' => '15']);
        Position::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset3->id, 'avg_price' => '25']);

        // Filter avg_price > 10
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions?avg_price_gt=10');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data');

        // Filter avg_price < 20
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions?avg_price_lt=20');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data');

        // Filter 10 < avg_price < 20
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/positions?avg_price_gt=10&avg_price_lt=20');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.avg_price', '15');
    }
}
