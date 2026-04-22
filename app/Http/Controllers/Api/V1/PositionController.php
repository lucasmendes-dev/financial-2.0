<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\PositionFilter;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PositionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, PositionFilter $filter): JsonResponse
    {
        $this->authorize('viewAny', Position::class);

        $positions = $filter->apply(
            Position::query()->where('user_id', $request->user()->id)->with('asset:id,ticker')
        )->paginate(20);

        return response()->json([
            'data' => PositionResource::collection($positions->appends($request->query())),
            'meta' => [
                'total' => $positions->total(),
                'per_page' => $positions->perPage(),
                'current_page' => $positions->currentPage(),
                'last_page' => $positions->lastPage(),
            ]
        ], Response::HTTP_OK);
    }

    public function show(Position $position): JsonResponse
    {
        $this->authorize('view', $position);

        return response()->json([
            'data' => new PositionResource($position),
        ], Response::HTTP_OK);
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->authorize('delete', $position);

        $position->delete();

        return response()->json(
            [
                'message' => 'Deleted successfully'
            ],
            Response::HTTP_OK
        );
    }
}
