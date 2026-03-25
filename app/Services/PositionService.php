<?php

namespace App\Services;

use App\Models\Position;

class PositionService
{
    public function updatePosition(array $data): void
    {
        $position = Position::where('user_id', $data['user_id'])->where('asset_id', $data['asset_id'])->first();

        if ($position) {
            $newQuantity = $data['type'] === 'buy' ? $position->quantity + $data['quantity'] : $position->quantity - $data['quantity'];

            $position->update([
                'quantity' => $newQuantity,
                'avg_price' => $this->calculateNewAvgPrice($position, $data),
            ]);
        } else {
            Position::create([
                'user_id' => $data['user_id'],
                'asset_id' => $data['asset_id'],
                'quantity' => $data['quantity'],
                'avg_price' => $data['price_per_asset'],
            ]);
        }
    }

    private function calculateNewAvgPrice(Position $position, array $data): float
    {
        if ($data['type'] === 'sell') {
            return (float) $position->avg_price;
        }

        $currentTotal = $position->quantity * $position->avg_price;
        $newTotal = $currentTotal + $data['total'];
        $newQuantity = $position->quantity + $data['quantity'];

        return $newQuantity > 0 ? $newTotal / $newQuantity : 0;
    }
}
