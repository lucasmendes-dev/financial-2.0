<?php

namespace App\Integrations;

use App\DTO\BrApiFreeDTO;
use App\Interfaces\MarketDataAdapterInterface;
use Illuminate\Support\Facades\Http;

class BrApiFreeAdapter implements MarketDataAdapterInterface
{
    private string $apiUrl;
    private array $params;

    public function __construct()
    {
        $this->apiUrl = config('services.brapi.url');
        $this->params['token'] = config('services.brapi.token');
    }

    public function fetchData(string $ticker): BrApiFreeDTO
    {
        $response = Http::get($this->apiUrl . $ticker, $this->params);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch data from BrApi');
        }

        return BrApiFreeDTO::fromArray($response->json());
    }
}
