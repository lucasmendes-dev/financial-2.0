<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'asset_ticker' => $this->asset->ticker ?? null,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'price_per_asset' => $this->price_per_asset->get(),
            'total' => $this->total->get(),
            'executed_at' => $this->executed_at,
        ];
    }
}
