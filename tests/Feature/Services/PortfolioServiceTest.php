<?php

namespace Tests\Feature\Services;

use App\Models\Asset;
use App\Models\MarketData;
use App\Models\Position;
use App\Models\User;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioServiceTest extends TestCase
{
    use RefreshDatabase;

    private PortfolioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PortfolioService::class);
    }

    public function test_get_portfolio_calculates_correct_values(): void
    {
        $user = User::factory()->create();
        
        $asset1 = Asset::factory()->create(['ticker' => 'AAPL', 'type' => 'stock']);
        $asset2 = Asset::factory()->create(['ticker' => 'MSFT', 'type' => 'stock']);

        Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset1->id,
            'quantity' => 10,
            'avg_price' => 15000, // stored as cents integer mostly or used as Money
        ]);

        Position::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset2->id,
            'quantity' => 5,
            'avg_price' => 20000,
        ]);

        MarketData::factory()->create([
            'asset_id' => $asset1->id,
            'regular_market_price' => 15500, // assuming cents
            'regular_market_change' => 500,
            'regular_market_change_percent' => 3.33,
            'logo_url' => 'http://example.com/aapl.png',
        ]);

        MarketData::factory()->create([
            'asset_id' => $asset2->id,
            'regular_market_price' => 18000,
            'regular_market_change' => -2000,
            'regular_market_change_percent' => -10.0,
            'logo_url' => 'http://example.com/msft.png',
        ]);

        $portfolio = $this->service->getPortfolio($user->id);

        $this->assertCount(2, $portfolio);

        $aaplDto = $portfolio->firstWhere('ticker', 'AAPL');
        $this->assertNotNull($aaplDto);
        $this->assertEquals(10, $aaplDto->quantity);

        $msftDto = $portfolio->firstWhere('ticker', 'MSFT');
        $this->assertNotNull($msftDto);
        $this->assertEquals(5, $msftDto->quantity);
    }

    public function test_get_portfolio_returns_empty_collection_for_user_with_no_positions(): void
    {
        $user = User::factory()->create();
        
        $portfolio = $this->service->getPortfolio($user->id);

        $this->assertCount(0, $portfolio);
    }
}
