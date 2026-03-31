<?php

namespace App\Integrations;

use App\Interfaces\MarketDataDTOInterface;
use App\Interfaces\MarketDataAdapterInterface;

class BrApiPaidAdapter implements MarketDataAdapterInterface
{
    public function fetchData(string $ticker): MarketDataDTOInterface
    {
        // Will Be implemented if acquire the paid plan from 'https://brapi.dev/pricing'
        throw new \Exception('Not implemented');
    }

    public function isTickerValid(string $ticker): bool
    {
        throw new \Exception('Not implemented');
    }

    public function saveFetchedDataToDB(string $assetId, MarketDataDTOInterface $data): void
    {
        throw new \Exception('Not implemented');
    }
}
