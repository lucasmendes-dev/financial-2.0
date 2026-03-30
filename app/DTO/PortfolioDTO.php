<?php

namespace App\DTO;

use App\Interfaces\MarketDataDTOInterface;

class PortfolioDTO implements MarketDataDTOInterface
{
    public function __construct(
        public readonly string $ticker,
        public readonly string $quantity,
        public readonly string $avg_price,
        public readonly string $current_price,
        public readonly string $total_cost,
        public readonly string $total_value,
        public readonly string $total_profit_loss_percent,
        public readonly string $total_profit_loss_value,
        public readonly string $daily_change_percent,
        public readonly string $daily_change_value,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ticker: $data['ticker'],
            quantity: $data['quantity'],
            avg_price: $data['avg_price'],
            current_price: $data['current_price'],
            total_cost: $data['total_cost'],
            total_value: $data['total_value'],
            total_profit_loss_percent: $data['total_profit_loss_percent'],
            total_profit_loss_value: $data['total_profit_loss_value'],
            daily_change_percent: $data['daily_change_percent'],
            daily_change_value: $data['daily_change_value'],
        );
    }

    public function toArray(): array
    {
        return [
            'ticker' => $this->ticker,
            'quantity' => $this->quantity,
            'avg_price' => $this->avg_price,
            'current_price' => $this->current_price,
            'total_cost' => $this->total_cost,
            'total_value' => $this->total_value,
            'total_profit_loss_percent' => $this->total_profit_loss_percent,
            'total_profit_loss_value' => $this->total_profit_loss_value,
            'daily_change_percent' => $this->daily_change_percent,
            'daily_change_value' => $this->daily_change_value,
        ];
    }
}
