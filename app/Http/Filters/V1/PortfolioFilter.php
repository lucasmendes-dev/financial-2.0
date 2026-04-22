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

    protected function with_profit_value(Collection $collection): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_profit_loss_value > 0);
    }

    protected function with_loss_value(Collection $collection): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_profit_loss_value < 0);
    }

    protected function with_profit_percent(Collection $collection): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_profit_loss_percent > 0);
    }

    protected function with_loss_percent(Collection $collection): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->total_profit_loss_percent < 0);
    }

    protected function with_daily_profit_percent(Collection $collection): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->daily_change_percent > 0);
    }

    protected function with_daily_loss_percent(Collection $collection): Collection
    {
        return $collection->filter(fn ($item) => (float) $item->daily_change_percent < 0);
    }

    protected function order_by(Collection $collection, string $value): Collection
    {
        $direction = strtolower($this->request->input('order', 'asc'));

        $descending = $direction === 'desc' || str_starts_with($value, '-');
        $property = ltrim($value, '-');

        return $collection->sortBy(function ($item) use ($property) {
            $val = $item->{$property} ?? null;
            return is_numeric($val) ? (float) $val : $val;
        }, SORT_REGULAR, $descending);
    }
}
