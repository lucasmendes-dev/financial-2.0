<?php

namespace App\Interfaces;

interface MarketDataProviderInterface
{
    public function fetchData(string $ticker): ?MarketDataDTOInterface;
}
