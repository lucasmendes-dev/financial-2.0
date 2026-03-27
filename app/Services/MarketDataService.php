<?php

namespace App\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataAdapterInterface;
use App\Models\MarketData;
use App\Models\MarketDataLog;
use Carbon\Carbon;

class MarketDataService
{
    public function __construct(
        private MarketDataAdapterInterface $marketDataAdapter
    ) {}

    public function getMarketData(string $ticker): MarketDataDTOInterface
    {
        return $this->marketDataAdapter->fetchData($ticker);
    }

    public function saveApiDataToDB(string $assetId, MarketDataDTOInterface $data): void
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
