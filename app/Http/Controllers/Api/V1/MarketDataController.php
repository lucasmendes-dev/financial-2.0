<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\FetchMarketDataJob;

class MarketDataController extends Controller
{
    public function updateMarketData()
    {
        dispatch(new FetchMarketDataJob())->onQueue('market-data');

        return response()->json([
            'message' => 'Market data fetch started'
        ]);
    }
}
