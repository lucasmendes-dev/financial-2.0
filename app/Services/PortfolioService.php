<?php

namespace App\Services;

use App\DTO\PortfolioDTO;
use App\Http\Filters\V1\PortfolioFilter;
use App\Models\MarketData;
use App\Models\Position;
use Illuminate\Support\Collection;

class PortfolioService
{
    public function getPortfolio(int $userId): Collection
    {
        $positions = Position::where('user_id', $userId)->get();
        $marketData = MarketData::whereIn('asset_id', $positions->pluck('asset_id'))->get();

        return $this->calculatePortfolioValues($positions, $marketData);
    }

    private function calculatePortfolioValues($positions, $marketData): Collection
    {
        return $positions->map(function ($position) use ($marketData) {
            $marketData = $marketData->firstWhere('asset_id', $position->asset_id);

            // coming from $casts (MoneyCast::class)
            $avgPrice = $position->avg_price;
            $changeValue = $marketData->regular_market_change;
            $currentPrice = $marketData->regular_market_price;

            $totalCost = $avgPrice->multiply($position->quantity);
            $totalValue = $currentPrice->multiply($position->quantity);
            $profit = $totalValue->subtract($totalCost);

            return PortfolioDTO::fromArray([
                'ticker' => $position->asset->ticker,
                'type' => $position->asset->type,
                'quantity' => $position->quantity,
                'avg_price' => $avgPrice->get(),
                'current_price' => $currentPrice->get(),
                'total_cost' => $totalCost->get(),
                'total_value' => $totalValue->get(),
                'total_profit_loss_percent' => $profit->percentage($totalCost)->get(),
                'total_profit_loss_value' => $profit->get(),
                'daily_change_percent' => $marketData->regular_market_change_percent,
                'daily_change_value' => $changeValue->multiply($position->quantity)->get(),
                'logo_url' => $marketData->logo_url,
            ]);
        });
    }
}
