<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    public function __construct(
        private AssetService $assetService,
        private PositionService $positionService,
    ) {}

    public function handleTransactionCreation(array $data): array
    {
        $data = $this->getTransactionData($data);
        $this->positionService->updatePosition($data);
        // send notification -> future
        return $data;
    }

    private function getTransactionData(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        $data['asset_id'] = $this->assetService->getAssetID($data['ticker']);
        $data['total'] = $data['quantity'] * $data['price_per_asset'];
        $data['executed_at'] = date('Y-m-d H:i:s', strtotime(now()));

        unset($data['ticker']);

        return $data;
    }

    public function handleTransactionUpdate(Transaction $transaction, array $data): Transaction
    {
        $transaction->fill($data);

        if ($transaction->isDirty('quantity') || $transaction->isDirty('price_per_asset')) {
            $transaction->total = $transaction->quantity * $transaction->price_per_asset;
        }

        $transaction->save();

        $this->positionService->recalculatePosition($transaction->user_id, $transaction->asset_id);

        return $transaction;
    }

    public function handleTransactionDeletion(Transaction $transaction): void
    {
        $userId = $transaction->user_id;
        $assetId = $transaction->asset_id;

        $transaction->delete();

        $this->positionService->recalculatePosition($userId, $assetId);
    }
}
