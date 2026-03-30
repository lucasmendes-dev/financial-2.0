<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'ticker' => $this->ticker,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'avg_price' => $this->avg_price,
            'current_price' => $this->current_price,
            'total_cost' => $this->total_cost,
            'total_value' => $this->total_value,
            'total_profit_loss_percent' => $this->total_profit_loss_percent,
            'total_profit_loss_value' => $this->total_profit_loss_value,
            'daily_change_percent' => $this->daily_change_percent,
            'daily_change_value' => $this->daily_change_value,
            'logo_url' => $this->logo_url,
        ];
    }
}
