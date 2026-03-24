<?php

namespace App\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataAdapterInterface;
use App\Models\Asset;
use Illuminate\Support\Facades\Http;

class AssetService
{
    public function __construct(
        private MarketDataService $marketDataService,
        private MarketDataAdapterInterface $marketDataAdapter
    ) {}

    public function getAssetID(string $ticker): string
    {
        $asset = Asset::where('ticker', $ticker)->exists();
        if (!$asset) {
            return $this->addNewAsset($ticker)->id;
        }

        return $asset->id;
    }

    public function addNewAsset(string $ticker): Asset
    {
        if ($this->isTickerValid($ticker)) {
            $assetData = $this->marketDataService->getMarketData($ticker, $this->marketDataAdapter);

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
