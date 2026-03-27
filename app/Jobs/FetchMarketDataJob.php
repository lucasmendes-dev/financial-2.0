<?php

namespace App\Jobs;

use App\Jobs\FetchSingleMarketDataJob;
use App\Models\Asset;
use App\Models\MarketDataLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Exception;
use Illuminate\Support\Facades\Log;

class FetchMarketDataJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function handle(): void
    {
        $assetTickers = Asset::query()->pluck('ticker');
        Log::info("Starting to fetch market data for " . $assetTickers->count() . " assets.");

        try {
            foreach ($assetTickers as $index => $ticker) {
                FetchSingleMarketDataJob::dispatch($ticker)
                    ->onQueue('market-data')
                    ->delay(now()->addSeconds($index * 2));
            }
        } catch (Exception $e) {
            Log::error("Failed to dispatch Market Data Jobs: " . $e->getMessage());
            MarketDataLog::create([
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
