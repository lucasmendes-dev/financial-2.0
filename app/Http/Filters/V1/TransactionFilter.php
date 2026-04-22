<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactionFilter
{
    public function __construct(protected Request $request) {}

    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->request->filled('asset_id'),
                fn (Builder $q) =>
                    $q->where('asset_id', $this->request->asset_id)
            )
            ->when(
                $this->request->filled('type'),
                fn (Builder $q) =>
                    $q->where('type', $this->request->type)
            )
            ->when(
                $this->request->filled('quantity_gt'),
                fn (Builder $q) =>
                    $q->where('quantity', '>', $this->request->quantity_gt)
            )
            ->when(
                $this->request->filled('quantity_lt'),
                fn (Builder $q) =>
                    $q->where('quantity', '<', $this->request->quantity_lt)
            )
            ->when(
                $this->request->filled('price_gt'),
                fn (Builder $q) =>
                    $q->where('price_per_asset', '>', $this->request->price_gt)
            )
            ->when(
                $this->request->filled('price_lt'),
                fn (Builder $q) =>
                    $q->where('price_per_asset', '<', $this->request->price_lt)
            )
            ->when(
                $this->request->filled('executed_at_gt'),
                fn (Builder $q) =>
                    $q->where('executed_at', '>', $this->request->executed_at_gt)
            )
            ->when(
                $this->request->filled('executed_at_lt'),
                fn (Builder $q) =>
                    $q->where('executed_at', '<', $this->request->executed_at_lt)
            );
    }
}
