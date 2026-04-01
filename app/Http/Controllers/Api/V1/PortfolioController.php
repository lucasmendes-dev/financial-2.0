<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\PortfolioFilter;
use App\Http\Resources\PortfolioResource;
use App\Services\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            ]
        ], Response::HTTP_OK);
    }
}
