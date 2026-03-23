<?php

namespace App\Services;

use App\Models\Asset;

class PositionService
{
    public function __construct(private AssetService $assetService) {}

    public function handlePositionData(array &$data, int $userId): void
    {
        $data['user_id'] = $userId;
        $data['asset_id'] = $this->handleAssetData($data['asset_ticker']);
        unset($data['asset_ticker']);
    }

    private function handleAssetData(string $ticker): string
    {
        if (!$this->assetExistsOnDatabase($ticker)) {
            return $this->assetService->addNewAsset($ticker)->id;
        }

        return Asset::where('ticker', $ticker)->first()->id;
    }

    private function assetExistsOnDatabase(string $ticker): bool
    {
        return Asset::where('ticker', $ticker)->exists();
    }
}

