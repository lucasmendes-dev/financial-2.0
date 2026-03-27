<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['asset_id', 'regular_market_price', 'regular_market_change', 'regular_market_change_percent', 'logo_url', 'fetched_at'])]
class MarketData extends Model
{
    /** @use HasFactory<\Database\Factories\MarketDataFactory> */
    use HasFactory, HasUuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
