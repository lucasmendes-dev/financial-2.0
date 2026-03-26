<?php

namespace App\Services;

use App\Models\Position;
use App\Models\Transaction;

class PositionService
{
    public function updatePosition(array $data): void
    {
        $position = Position::where('user_id', $data['user_id'])->where('asset_id', $data['asset_id'])->first();

        if ($position) {
            $newQuantity = $data['type'] === 'buy' ? $position->quantity + $data['quantity'] : $position->quantity - $data['quantity'];

            if ($newQuantity <= 0) {
                $position->delete();
                return;
            }

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

    public function recalculatePosition(string $userId, string $assetId): void
    {
        $transactions = Transaction::where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->orderBy('executed_at')
            ->get();

        $quantity = 0;
        $avgPrice = 0.0;

        foreach ($transactions as $tx) {
            if ($tx->type === 'buy') {
                $totalCost = ($quantity * $avgPrice) + ($tx->quantity * $tx->price_per_asset);
                $quantity += $tx->quantity;
                $avgPrice = $quantity > 0 ? $totalCost / $quantity : 0;
            } else {
                $quantity -= $tx->quantity;
                // avg_price stays the same on sell
            }
        }

        $position = Position::where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->first();

        if ($quantity <= 0) {
            if ($position) {
                $position->delete();
            }
        } else {
            if ($position) {
                $position->update([
                    'quantity' => $quantity,
                    'avg_price' => $avgPrice,
                ]);
            } else {
                Position::create([
                    'user_id' => $userId,
                    'asset_id' => $assetId,
                    'quantity' => $quantity,
                    'avg_price' => $avgPrice,
                ]);
            }
        }
    }

    public function reverseTransaction(Transaction $transaction): void
    {
        $position = Position::where('user_id', $transaction->user_id)
            ->where('asset_id', $transaction->asset_id)
            ->first();

        if (!$position) {
            return;
        }

        if ($transaction->type === 'buy') {
            $newQuantity = $position->quantity - $transaction->quantity;
            
            $currentTotal = $position->quantity * $position->avg_price;
            $txTotal = $transaction->quantity * $transaction->price_per_asset;
            $newTotal = $currentTotal - $txTotal;
            
            $newAvgPrice = $newQuantity > 0 ? $newTotal / $newQuantity : 0;
        } else {
            $newQuantity = $position->quantity + $transaction->quantity;
            $newAvgPrice = $position->avg_price;
        }

        if ($newQuantity <= 0) {
            $position->delete();
        } else {
            $position->update([
                'quantity' => $newQuantity,
                'avg_price' => $newAvgPrice,
            ]);
        }
    }
}
