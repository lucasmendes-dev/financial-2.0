<?php

namespace App\Jobs;

use App\Jobs\FetchSingleMarketDataJob;
use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FetchMarketDataJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function handle(): void
    {
        $assetTickers = Asset::query()->pluck('ticker');

        foreach ($assetTickers as $ticker) {
            FetchSingleMarketDataJob::dispatch($ticker)
                ->onQueue('market-data')
                ->delay(now()->addSeconds(2));
        }
    }
}
