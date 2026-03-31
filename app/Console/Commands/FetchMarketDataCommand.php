<?php

namespace App\Console\Commands;

use App\Jobs\FetchMarketDataJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('market-data:fetch')]
#[Description('Fetch market data for all assets')]
class FetchMarketDataCommand extends Command
{
    public function handle()
    {
        dispatch(new FetchMarketDataJob())->onQueue('market-data');

        return Command::SUCCESS;
    }
}
