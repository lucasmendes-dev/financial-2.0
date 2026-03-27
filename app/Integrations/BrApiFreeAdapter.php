<?php

namespace App\Integrations;

use App\DTO\BrApiFreeDTO;
use App\Interfaces\MarketDataAdapterInterface;
use App\Interfaces\MarketDataDTOInterface;
use App\Models\MarketData;
use App\Models\MarketDataLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BrApiFreeAdapter implements MarketDataAdapterInterface
{
    private string $availableTickersUrl = "https://brapi.dev/api/available";
    private string $apiUrl;
    private array $params;

    public function __construct()
    {
        $this->apiUrl = config('services.brapi.url');
        $this->params['token'] = config('services.brapi.token');
    }

    public function fetchData(string $ticker): BrApiFreeDTO
    {
        $response = Http::get($this->apiUrl . $ticker, $this->params);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch data from BrApi');
        }

        return BrApiFreeDTO::fromArray($response->json());
    }

    public function isTickerValid(string $ticker): bool
    {
        $response = Http::get($this->availableTickersUrl)->json();

        return in_array($ticker, $response['stocks']);
    }

    public function saveFetchedDataToDB(string $assetId, MarketDataDTOInterface $data): void
    {
        MarketData::updateOrCreate(
            ['asset_id' => $assetId],
            [
                'regular_market_price' => $data->regular_market_price,
                'regular_market_change' => $data->regular_market_change,
                'regular_market_change_percent' => $data->regular_market_change_percent,
                'logo_url' => $data->logourl,
                'fetched_at' => Carbon::parse($data->requested_at)->toDateTimeString(),
            ]
        );

        MarketDataLog::create([
            'type' => 'success',
            'message' => implode(' | ', $data->toArray())
        ]);
    }
}
