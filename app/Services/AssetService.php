<?php

namespace App\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataProviderInterface;
use App\Models\Asset;
use Illuminate\Support\Facades\Http;

class AssetService
{
    public function __construct(
        private MarketDataService $marketDataService,
        private MarketDataProviderInterface $marketDataProvider
    ) {}

    public function addNewAsset(string $ticker): Asset
    {
        if ($this->isTickerValid($ticker)) {
            $assetData = $this->marketDataService->getMarketData($ticker, $this->marketDataProvider);

            return Asset::create([
                'ticker' => $ticker,
                'name' => $assetData->long_name,
                'type' => $this->detectAssetType($assetData),
            ]);
        }

        throw new \Exception("Ticker '{$ticker}' is not valid");
    }

    private function isTickerValid(string $ticker): bool
    {
        // calls brApi to check if ticker is valid
        $url = "https://brapi.dev/api/available";
        $response = Http::get($url)->json();

        if (in_array($ticker, $response['stocks'])) {
            return true;
        }

        return false;
    }

    private function detectAssetType(MarketDataDTOInterface $data): string
    {
        if (str_ends_with($data->symbol, '11')) {
            if (str_contains($data->long_name, 'Investimento Imobiliario')) {
                return 'fii';
            }

            return 'stock';
        }

        return 'stock';
    }
}
