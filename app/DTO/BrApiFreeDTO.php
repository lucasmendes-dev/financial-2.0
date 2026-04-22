<?php

namespace App\DTO;

use App\Interfaces\MarketDataDTOInterface;

class BrApiFreeDTO implements MarketDataDTOInterface
{
    public function __construct(
        public readonly string $symbol,
        public readonly string $long_name,
        public readonly float $regular_market_price,
        public readonly float $regular_market_change,
        public readonly float $regular_market_change_percent,
        public readonly string $logourl,
        public readonly string $requested_at,
    ) {}

    public static function fromArray(array $data, int $resultIndex = 0): self
    {
        $result = $data['results'][$resultIndex];

        return new self(
            symbol: $result['symbol'],
            long_name: $result['longName'],
            regular_market_price: (float) $result['regularMarketPrice'],
            regular_market_change: (float) $result['regularMarketChange'],
            regular_market_change_percent: (float) $result['regularMarketChangePercent'],
            logourl: $result['logourl'],
            requested_at: $data['requestedAt']
        );
    }

    public function toArray(): array
    {
        return [
            'symbol' => $this->symbol,
            'long_name' => $this->long_name,
            'regular_market_price' => $this->regular_market_price,
            'regular_market_change' => $this->regular_market_change,
            'regular_market_change_percent' => $this->regular_market_change_percent,
            'logourl' => $this->logourl,
            'requested_at' => $this->requested_at,
        ];
    }
}
