<?php

namespace App\Interfaces;

interface MarketDataAdapterInterface
{
    public function fetchData(string $ticker): MarketDataDTOInterface;
}
