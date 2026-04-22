<?php

namespace Tests\Feature\Jobs;

use App\Jobs\FetchMarketDataJob;
use App\Jobs\FetchSingleMarketDataJob;
use App\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FetchMarketDataJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_single_market_data_jobs_for_all_assets()
    {
        Queue::fake();

        Asset::factory()->create(['ticker' => 'AAPL']);
        Asset::factory()->create(['ticker' => 'GOOGL']);

        $job = new FetchMarketDataJob();
        $job->handle();

        Queue::assertPushed(FetchSingleMarketDataJob::class, 2);
        
        // Assert the jobs are pushed to the 'market-data' queue
        Queue::assertPushedOn('market-data', FetchSingleMarketDataJob::class);
    }
}
