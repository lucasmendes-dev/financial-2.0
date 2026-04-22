<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\AssetFilter;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, AssetFilter $filter): JsonResponse
    {
        $this->authorize('viewAny', Asset::class);

        $assets = $filter->apply(Asset::query())->paginate(20);

        return response()->json([
            'data' => AssetResource::collection($assets->appends($request->query())),
            'meta' => [
                'total' => $assets->total(),
                'per_page' => $assets->perPage(),
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
            ]
        ], Response::HTTP_OK);
    }

    public function show(Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);

        return response()->json([
            'data' => new AssetResource($asset),
        ], Response::HTTP_OK);
    }
}
