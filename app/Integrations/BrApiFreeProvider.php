<?php

namespace App\Integrations;

use App\DTO\BrApiFreeDTO;
use App\Interfaces\MarketDataProviderInterface;
use Illuminate\Support\Facades\Http;

class BrApiFreeProvider implements MarketDataProviderInterface
{
    private string $apiUrl;
    private array $params;

    public function __construct()
    {
        $this->apiUrl = config('services.brapi.url');
        $this->params['token'] = config('services.brapi.token');
    }

    public function fetchData(string $ticker): ?BrApiFreeDTO
    {
        $response = Http::get($this->apiUrl . $ticker, $this->params);

        if ($response->failed()) {
            return null;
        }

        return BrApiFreeDTO::fromArray($response->json());
    }
}
