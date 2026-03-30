<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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

        return response()->json([
            'message' => 'Portfolio retrieved successfully',
            'data' => PortfolioResource::collection($portfolio),
            'meta' => [
                'total' => $portfolio->count(),
            ]
        ], Response::HTTP_OK);
    }
}
