<?php

namespace Tests\Unit\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataAdapterInterface;
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

    public function test_get_market_data_returns_dto_when_adapter_returns_data()
    {
        $ticker = 'PETR4';
        
        $mockDto = Mockery::mock(MarketDataDTOInterface::class);
        $mockAdapter = Mockery::mock(MarketDataAdapterInterface::class);
        
        $mockAdapter->shouldReceive('fetchData')
            ->once()
            ->with($ticker)
            ->andReturn($mockDto);

        $service = new MarketDataService($mockAdapter);
        $result = $service->getMarketData($ticker);

        $this->assertSame($mockDto, $result);
    }

    public function test_get_market_data_throws_exception_when_adapter_fails()
    {
        $ticker = 'INVALID';
        
        $mockAdapter = Mockery::mock(MarketDataAdapterInterface::class);
        
        $mockAdapter->shouldReceive('fetchData')
            ->once()
            ->with($ticker)
            ->andThrow(new \Exception('Failed to fetch data from BrApi'));

        $service = new MarketDataService($mockAdapter);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch data from BrApi');

        $service->getMarketData($ticker);
    }
}
