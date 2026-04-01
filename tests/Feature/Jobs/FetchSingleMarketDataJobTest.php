<?php

namespace Tests\Feature\Jobs;

use App\Jobs\FetchSingleMarketDataJob;
use App\Models\Asset;
use App\Services\MarketDataService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class FetchSingleMarketDataJobTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_does_nothing_if_asset_not_found()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with("Asset not found for ticker: AAPL");

        $marketDataServiceMock = Mockery::mock(MarketDataService::class);
        $marketDataServiceMock->shouldNotReceive('getMarketData');

        $job = new FetchSingleMarketDataJob('AAPL');
        $job->handle($marketDataServiceMock);
    }
    
    public function test_logs_error_and_throws_exception_on_failure()
    {
        $asset = Asset::factory()->create(['ticker' => 'AAPL']);

        $marketDataServiceMock = Mockery::mock(MarketDataService::class);
        $marketDataServiceMock->shouldReceive('getMarketData')
            ->andThrow(new Exception('API Error'));

        Log::shouldReceive('error')
            ->once()
            ->with("Failed to fetch market data for AAPL: API Error");

        $job = new FetchSingleMarketDataJob('AAPL');
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API Error');
        
        $job->handle($marketDataServiceMock);

        $this->assertDatabaseHas('market_data_logs', [
            'type' => 'error',
            'message' => 'Failed for AAPL: API Error'
        ]);
    }
}
