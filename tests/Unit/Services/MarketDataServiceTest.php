<?php

namespace Tests\Unit\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataProviderInterface;
use App\Services\MarketDataService;
use Mockery;
use Tests\TestCase;

class MarketDataServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_market_data_returns_dto_when_provider_returns_data()
    {
        $ticker = 'PETR4';
        
        $mockDto = Mockery::mock(MarketDataDTOInterface::class);
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        
        $mockProvider->shouldReceive('fetchData')
            ->once()
            ->with($ticker)
            ->andReturn($mockDto);

        $service = new MarketDataService();
        $result = $service->getMarketData($ticker, $mockProvider);

        $this->assertSame($mockDto, $result);
    }

    public function test_get_market_data_returns_null_when_provider_returns_null()
    {
        $ticker = 'INVALID';
        
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        
        $mockProvider->shouldReceive('fetchData')
            ->once()
            ->with($ticker)
            ->andReturn(null);

        $service = new MarketDataService();
        $result = $service->getMarketData($ticker, $mockProvider);

        $this->assertNull($result);
    }
}
