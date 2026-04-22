<?php

namespace App\Services;

use App\Models\Position;
use App\Models\Transaction;
use App\ValueObjects\Money;

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
                'avg_price' => (new Money((string) $data['price_per_asset']))->get(),
            ]);
        }
    }

    private function calculateNewAvgPrice(Position $position, array $data): string
    {
        if ($data['type'] === 'sell') {
            return $position->avg_price->get();
        }

        $total = new Money($data['total']);

        $currentTotal = $position->avg_price->multiply($position->quantity);
        $newTotal = $currentTotal->add($total);
        $newQuantity = $position->quantity + $data['quantity'];

        return $newQuantity > 0 ? $newTotal->divide($newQuantity)->get() : '0';
    }

    public function recalculatePosition(string $userId, string $assetId): void
    {
        $transactions = Transaction::where('user_id', $userId)
            ->where('asset_id', $assetId)
            ->orderBy('executed_at')
            ->get();

        $quantity = 0;
        $avgPrice = new Money('0');

        foreach ($transactions as $tx) {
            if ($tx->type === 'buy') {
                $totalCost = $avgPrice->multiply($quantity)->add($tx->price_per_asset->multiply($tx->quantity));
                $quantity += $tx->quantity;
                $avgPrice = $quantity > 0 ? $totalCost->divide($quantity) : new Money('0');
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
                    'avg_price' => $avgPrice->get(),
                ]);
            } else {
                Position::create([
                    'user_id' => $userId,
                    'asset_id' => $assetId,
                    'quantity' => $quantity,
                    'avg_price' => $avgPrice->get(),
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
            
            $currentTotal = $position->avg_price->multiply($position->quantity);
            $txTotal = $transaction->price_per_asset->multiply($transaction->quantity);
            $newTotal = $currentTotal->subtract($txTotal);
            
            $newAvgPrice = $newQuantity > 0 ? $newTotal->divide($newQuantity)->get() : '0';
        } else {
            $newQuantity = $position->quantity + $transaction->quantity;
            $newAvgPrice = $position->avg_price->get();
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
