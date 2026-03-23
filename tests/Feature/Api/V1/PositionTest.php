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
                    'avg_price' => (float) $position->avg_price,
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

    public function test_can_create_position()
    {
        $asset = Asset::factory()->create();
        $data = [
            'asset_id' => $asset->id,
            'asset_ticker' => $asset->ticker,
            'quantity' => 10.5,
            'avg_price' => 100.25,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/positions', $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'asset_id' => $asset->id,
                    'asset_ticker' => $asset->ticker,
                    'quantity' => 10.5,
                    'avg_price' => 100.25,
                ]
            ]);

        $this->assertDatabaseHas('positions', [
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
            'quantity' => 10.5,
            'avg_price' => 100.25,
        ]);
    }

    public function test_can_update_position()
    {
        $asset = Asset::factory()->create();
        $position = Position::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
            'quantity' => 5,
            'avg_price' => 50,
        ]);

        $data = [
            'quantity' => 15,
            'avg_price' => 55.5,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/positions/{$position->id}", $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Updated successfully',
                'data' => [
                    'quantity' => 15,
                    'avg_price' => 55.5,
                ]
            ]);

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'quantity' => 15,
            'avg_price' => 55.5,
        ]);
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
}
