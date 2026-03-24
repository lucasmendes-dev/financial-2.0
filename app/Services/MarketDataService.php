<?php

namespace App\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataAdapterInterface;

class MarketDataService
{
    public function __construct(
        private MarketDataAdapterInterface $marketDataAdapter
    ) {}

    public function getMarketData(string $ticker): MarketDataDTOInterface
    {
        return $this->marketDataAdapter->fetchData($ticker);
    }
}
