<?php

namespace Tests\Feature\BrApi;

use App\Integrations\BrApiFreeAdapter;
use App\DTO\BrApiFreeDTO;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BrApiFreeAdapterTest extends TestCase
{
    public function test_it_returns_dto_on_successful_api_response()
    {
        config(['services.brapi.url' => 'https://brapi.dev/api/quote/']);
        config(['services.brapi.token' => 'test-token']);

        $ticker = 'PETR4';
        $mockResponse = [
            'results' => [
                [
                    'symbol' => 'PETR4',
                    'longName' => 'Petroleo Brasileiro SA Pfd',
                    'regularMarketPrice' => 30.50,
                    'regularMarketChange' => 0.50,
                    'regularMarketChangePercent' => 1.67,
                    'logourl' => 'https://logo.url/petr4.png',
                ]
            ],
            'requestedAt' => '2023-10-27T10:00:00Z'
        ];

        Http::fake([
            'https://brapi.dev/api/quote/PETR4*' => Http::response($mockResponse, Response::HTTP_OK)
        ]);

        $adapter = new BrApiFreeAdapter();
        $result = $adapter->fetchData($ticker);

        $this->assertInstanceOf(BrApiFreeDTO::class, $result);
        $this->assertEquals('PETR4', $result->symbol);
        $this->assertEquals(30.50, $result->regular_market_price);
        $this->assertEquals(0.50, $result->regular_market_change);
        $this->assertEquals(1.67, $result->regular_market_change_percent);
        $this->assertEquals('https://logo.url/petr4.png', $result->logourl);
        $this->assertEquals('2023-10-27T10:00:00Z', $result->requested_at);
    }

    public function test_it_throws_exception_on_api_failure()
    {
        config(['services.brapi.url' => 'https://brapi.dev/api/quote/']);

        Http::fake([
            'https://brapi.dev/api/quote/*' => Http::response(['message' => 'Error'], Response::HTTP_INTERNAL_SERVER_ERROR)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch data from BrApi');

        $adapter = new BrApiFreeAdapter();
        $adapter->fetchData('INVALID');
    }

    public function test_it_throws_exception_on_unauthorized_response()
    {
        config(['services.brapi.url' => 'https://brapi.dev/api/quote/']);

        Http::fake([
            'https://brapi.dev/api/quote/*' => Http::response(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch data from BrApi');

        $adapter = new BrApiFreeAdapter();
        $adapter->fetchData('PETR4');
    }
}
