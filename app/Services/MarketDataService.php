<?php

namespace App\Services;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataProviderInterface;

class MarketDataService
{
    public function __construct() {}

    public function getMarketData(string $ticker, MarketDataProviderInterface $marketDataProvider): ?MarketDataDTOInterface
    {
        $data = $marketDataProvider->fetchData($ticker);

        if ($data === null) {
            return null;
        }

        return $data;
    }
}
