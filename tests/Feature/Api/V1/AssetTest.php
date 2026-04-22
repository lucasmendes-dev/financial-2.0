<?php

namespace Tests\Feature\Api\V1;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_assets()
    {
        Asset::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/assets');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['ticker', 'name', 'type']
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_show_asset()
    {
        $asset = Asset::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/assets/{$asset->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'ticker' => $asset->ticker,
                    'name' => $asset->name,
                    'type' => strtoupper($asset->type),
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_access_assets()
    {
        $response = $this->getJson('/api/v1/assets');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
