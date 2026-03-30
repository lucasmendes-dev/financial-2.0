<?php

namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{

    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        return new Money($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof Money) {
            return $value->get();
        }
        return (string) $value;
    }
}
