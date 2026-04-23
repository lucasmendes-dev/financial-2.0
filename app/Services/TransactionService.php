<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private AssetService $assetService,
        private PositionService $positionService,
    ) {}

    public function handleTransactionCreation(array $data, int|string $userId): Transaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $data = $this->getTransactionData($data, $userId);
            $transaction = Transaction::create($data);
            $this->positionService->updatePosition($data);

            $this->clearPortfolioCache($userId);

            return $transaction;
        });
    }

    private function getTransactionData(array $data, int|string $userId): array
    {
        $data['user_id'] = $userId;
        $data['asset_id'] = $this->assetService->getOrCreateAssetID($data['ticker']);
        $data['total'] = $data['quantity'] * $data['price_per_asset'];
        $data['executed_at'] = date('Y-m-d H:i:s', strtotime(now()));

        unset($data['ticker']);

        return $data;
    }

    public function handleTransactionUpdate(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->fill($data);

            if ($transaction->isDirty('quantity') || $transaction->isDirty('price_per_asset')) {
                $transaction->total = $transaction->price_per_asset->multiply($transaction->quantity);
            }

            $transaction->save();

            $this->positionService->recalculatePosition($transaction->user_id, $transaction->asset_id);

            $this->clearPortfolioCache($transaction->user_id);

            return $transaction;
        });
    }

    public function handleTransactionDeletion(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $userId = $transaction->user_id;
            $assetId = $transaction->asset_id;

            $transaction->delete();

            $this->positionService->recalculatePosition($userId, $assetId);

            $this->clearPortfolioCache($userId);
        });
    }

    private function clearPortfolioCache(int|string $userId): void
    {
        Cache::tags(['portfolios'])->forget("portfolio:user:{$userId}");
    }
}
