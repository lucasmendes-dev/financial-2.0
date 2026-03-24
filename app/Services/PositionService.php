<?php

namespace App\Services;

use App\Models\Asset;

class PositionService
{
    public function __construct(private AssetService $assetService) {}

    public function handlePositionData(array &$data, int $userId): void
    {
        $data['user_id'] = $userId;
        $data['asset_id'] = $this->assetService->getAssetID($data['asset_ticker']);
        unset($data['asset_ticker']);
    }
}

