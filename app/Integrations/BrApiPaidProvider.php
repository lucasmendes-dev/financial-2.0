<?php

namespace App\Integrations;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataProviderInterface;

class BrApiPaidProvider implements MarketDataProviderInterface
{
    public function fetchData(string $ticker): ?MarketDataDTOInterface
    {
        // Will Be implemented if acquire the paid plan from 'https://brapi.dev/pricing'
        return null;
    }
}
