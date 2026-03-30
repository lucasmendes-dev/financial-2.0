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
        // aplicar cache aqui mais pra frente
        $portfolio = $this->portfolioService->getPortfolio($request->user()->id);

        $filters = new PortfolioFilter($request);
        $portfolio = $filters->apply($portfolio);

        return response()->json([
            'message' => 'Portfolio retrieved successfully',
            'data' => PortfolioResource::collection($portfolio),
            'meta' => [
                'total' => $portfolio->count(),
            ]
        ], Response::HTTP_OK);
    }
}
