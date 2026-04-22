<?php

namespace App\Integrations;

use App\DTO\BrApiFreeDTO;
use App\Interfaces\MarketDataAdapterInterface;
use Illuminate\Support\Facades\Http;

class BrApiFreeAdapter implements MarketDataAdapterInterface
{
    private string $availableTickersUrl = "https://brapi.dev/api/available";
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

    public function isTickerValid(string $ticker): bool
    {
        $response = Http::get($this->availableTickersUrl)->json();

        return in_array($ticker, $response['stocks']);
    }
}
