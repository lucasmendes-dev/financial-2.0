<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\MarketData;
use App\Services\MarketDataService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FetchSingleMarketDataJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        private string $ticker
    ) {}

    public function handle(MarketDataService $marketDataService)
    {
        try {
            $data = $marketDataService->getMarketData($this->ticker);
            $asset = Asset::where('ticker', $this->ticker)->first();

            if (!$asset) {
                Log::warning("Asset not found for ticker: {$this->ticker}");
                return;
            }

            MarketData::create([
                'asset_id' => $asset->id,
                'regular_market_price' => $data->regular_market_price,
                'regular_market_change' => $data->regular_market_change,
                'regular_market_change_percent' => $data->regular_market_change_percent,
                'logo_url' => $data->logourl,
                'fetched_at' => Carbon::parse($data->requested_at)->toDateTimeString(),
            ]);
        } catch (Exception $e) {
            Log::error("Failed to fetch market data for {$this->ticker}: " . $e->getMessage());
            throw $e;
        }
    }
}
