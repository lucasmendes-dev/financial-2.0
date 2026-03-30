<?php

namespace Tests\Feature\Api\V1;

use App\Models\Asset;
use App\Models\MarketData;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_portfolio_index()
    {
        $asset = Asset::factory()->create(['ticker' => 'AAPL', 'type' => 'stock']);

        Position::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
            'quantity' => 10,
            'avg_price' => 15000,
        ]);

        MarketData::factory()->create([
            'asset_id' => $asset->id,
            'regular_market_price' => 15500,
            'regular_market_change' => 500,
            'regular_market_change_percent' => 3.33,
            'logo_url' => 'http://example.com/aapl.png',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/portfolio');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'ticker',
                        'type',
                        'quantity',
                        'avg_price',
                        'current_price',
                        'total_cost',
                        'total_value',
                        'total_profit_loss_percent',
                        'total_profit_loss_value',
                        'daily_change_percent',
                        'daily_change_value',
                        'logo_url',
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ]
            ]);

        $response->assertJsonPath('data.0.ticker', 'AAPL');
    }

    public function test_can_paginate_portfolio()
    {
        // Add 25 positions for pagination test
        for ($i = 0; $i < 25; $i++) {
            $asset = Asset::factory()->create(['ticker' => "TICK{$i}", 'type' => 'stock']);
            Position::factory()->create([
                'user_id' => $this->user->id,
                'asset_id' => $asset->id,
                'quantity' => 10,
                'avg_price' => 1000,
            ]);
            MarketData::factory()->create([
                'asset_id' => $asset->id,
                'regular_market_price' => 1100,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/portfolio?per_page=10');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_can_filter_portfolio()
    {
        $asset1 = Asset::factory()->create(['ticker' => 'AAPL', 'type' => 'stock']);
        Position::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset1->id,
            'quantity' => 10,
            'avg_price' => 15000,
        ]);
        MarketData::factory()->create(['asset_id' => $asset1->id, 'regular_market_price' => 15500]);

        $asset2 = Asset::factory()->create(['ticker' => 'MSFT', 'type' => 'stock']);
        Position::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset2->id,
            'quantity' => 5,
            'avg_price' => 20000,
        ]);
        MarketData::factory()->create(['asset_id' => $asset2->id, 'regular_market_price' => 18000]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/portfolio?ticker=AAPL');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.ticker', 'AAPL');
    }

    public function test_portfolio_returns_unauthorized_if_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/portfolio');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
