<?php

namespace App\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataAdapterInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class MarketDataService
{
    public function __construct(
        private MarketDataAdapterInterface $marketDataAdapter
    ) {}

    public function getMarketData(string $ticker): MarketDataDTOInterface
    {
        return $this->marketDataAdapter->fetchData($ticker);
    }

    public function saveData(string $assetId, MarketDataDTOInterface $data): void
    {
        $userId = Auth::user()->id;
        Cache::forget("portfolio:user:{$userId}");

        $this->marketDataAdapter->saveFetchedDataToDB($assetId, $data);
    }
}
