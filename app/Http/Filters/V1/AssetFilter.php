<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AssetFilter
{
    public function __construct(protected Request $request) {}

    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->request->filled('ticker'),
                fn (Builder $q) =>
                    $q->where('ticker', $this->request->ticker)
            )
            ->when(
                $this->request->filled('name'),
                fn (Builder $q) =>
                    $q->where('name', 'like', '%' . $this->request->name . '%')
            )
            ->when(
                $this->request->filled('type'),
                fn (Builder $q) =>
                    $q->where('type', $this->request->type)
            );
    }
}
