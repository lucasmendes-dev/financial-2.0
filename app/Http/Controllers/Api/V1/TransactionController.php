<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TransactionFilter;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index(Request $request, TransactionFilter $filter): JsonResponse
    {
        $this->authorize('viewAny', Transaction::class);

        $transactions = $filter->apply(
            Transaction::query()->where('user_id', $request->user()->id)->with('asset:id,ticker')
        )->paginate(20);

        return response()->json([
            'data' => TransactionResource::collection($transactions->appends($request->query())),
            'meta' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ], Response::HTTP_OK);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $this->authorize('create', Transaction::class);

        $data = $this->transactionService->handleTransactionCreation($request->validated());
        $transaction = Transaction::create($data);

        return response()->json([
            'message' => 'Transaction created successfully',
            'data' => new TransactionResource($transaction),
        ], Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);

        return response()->json([
            'data' => new TransactionResource($transaction),
        ], Response::HTTP_OK);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $transaction = $this->transactionService->handleTransactionUpdate($transaction, $request->validated());

        return response()->json([
            'message' => 'Transaction updated successfully',
            'data' => new TransactionResource($transaction),
        ], Response::HTTP_OK);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        $this->transactionService->handleTransactionDeletion($transaction);

        return response()->json([
            'message' => 'Deleted successfully'
        ], Response::HTTP_OK);
    }
}
