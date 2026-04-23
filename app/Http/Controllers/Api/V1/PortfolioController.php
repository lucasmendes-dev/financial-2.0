<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\PortfolioFilter;
use App\Http\Resources\PortfolioResource;
use App\Services\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PortfolioController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function index(Request $request): JsonResponse
    {
        $portfolio = $this->portfolioService->getPortfolio($request->user()->id);

        $filters = new PortfolioFilter($request);
        $portfolio = $filters->apply($portfolio);

        $perPage = $request->input('per_page', 20);
        $paginated = $portfolio->paginate($perPage);

        return response()->json([
            'data' => PortfolioResource::collection($paginated->appends($request->query())),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'requested_at' => now()->format('Y-m-d H:i:s'),
                'last_api_request' => DB::table('market_data')->value('fetched_at'),
            ]
        ], Response::HTTP_OK);
    }
}
