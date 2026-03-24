<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PositionFilter
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
                $this->request->filled('user_id'),
                fn (Builder $q) =>
                    $q->where('user_id', $this->request->user_id)
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
                $this->request->filled('avg_price_gt'),
                fn (Builder $q) =>
                    $q->where('avg_price', '>', $this->request->avg_price_gt)
            )
            ->when(
                $this->request->filled('avg_price_lt'),
                fn (Builder $q) =>
                    $q->where('avg_price', '<', $this->request->avg_price_lt)
            );
    }
}
