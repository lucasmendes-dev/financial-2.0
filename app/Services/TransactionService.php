<?php

namespace App\Services;

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
}
