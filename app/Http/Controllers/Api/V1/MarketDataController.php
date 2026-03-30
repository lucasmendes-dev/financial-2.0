<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\FetchMarketDataJob;
use Illuminate\Http\JsonResponse;
class MarketDataController extends Controller
{
    public function updateMarketData(): JsonResponse
    {
        dispatch(new FetchMarketDataJob())->onQueue('market-data');

        return response()->json([
            'message' => 'Market data fetch started'
        ]);
    }
}
