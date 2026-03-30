<?php

namespace App\Http\Filters\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PortfolioFilter
{
    public function __construct(protected Request $request) {}

    public function apply(Collection $collection): Collection
    {
        foreach ($this->request->all() as $filter => $value) {
            $method = Str::snake($filter);

            if ($method !== 'apply' && method_exists($this, $method) && filled($value)) {
                $collection = $this->$method($collection, $value);
            }
        }

        return $collection->values();
    }

    protected function ticker(Collection $collection, string $value): Collection
    {
        return $collection->filter(fn ($item) => $item->ticker === strtoupper($value));
    }

    protected function type(Collection $collection, string $value): Collection
    {
        return $collection->filter(fn ($item) => $item->type === strtolower($value));
    }

    protected function quantity_gt(Collection $collection, string|float $value): Collection
    {
        return $collection->filter(fn ($item) => (int) $item->quantity > (int) $value);
    }

    protected function quantity_lt(Collection $collection, string|float $value): Collection
    {
        return $collection->filter(fn ($item) => (int) $item->quantity < (int) $value);
    }

    protected function total_cost_gt(Collection $collection, string|float $value): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_cost > (float) $value);
    }

    protected function total_cost_lt(Collection $collection, string|float $value): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_cost < (float) $value);
    }

    protected function total_value_gt(Collection $collection, string|float $value): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_value > (float) $value);
    }

    protected function total_value_lt(Collection $collection, string|float $value): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_value < (float) $value);
    }
}
