<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\MarketDataLog;
use App\Services\MarketDataService;
use Exception;
use Illuminate\Bus\Queueable;
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

    public function handle(MarketDataService $marketDataService): void
    {
        try {
            $asset = Asset::where('ticker', $this->ticker)->first();
            if (!$asset) {
                Log::warning("Asset not found for ticker: {$this->ticker}");
                return;
            }

            $data = $marketDataService->getMarketData($this->ticker);
            $marketDataService->saveApiDataToDB($asset->id, $data);
        } catch (Exception $e) {
            Log::error("Failed to fetch market data for {$this->ticker}: " . $e->getMessage());
            
            MarketDataLog::create([
                'type' => 'error',
                'message' => "Failed for {$this->ticker}: " . $e->getMessage()
            ]);

            throw $e;
        }
    }
}
