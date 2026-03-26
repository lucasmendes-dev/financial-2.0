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

    public function getOrCreateAssetID(string $ticker): string
    {
        $asset = Asset::where('ticker', $ticker)->first();
        if (!$asset) {
            return $this->addNewAsset($ticker)->id;
        }

        return $asset->id;
    }

    public function addNewAsset(string $ticker): Asset
    {
        if ($this->marketDataAdapter->isTickerValid($ticker)) {
            $assetData = $this->marketDataService->getMarketData($ticker, $this->marketDataAdapter);

            return Asset::create([
                'ticker' => $ticker,
                'name' => $assetData->long_name,
                'type' => $this->detectAssetType($assetData),
            ]);
            // implement code to also add to market_data table at this point since it already does a request?
        }

        throw new \Exception("Ticker '{$ticker}' is not valid");
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
