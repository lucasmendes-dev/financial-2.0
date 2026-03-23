<?php

namespace App\Providers;

use App\Integrations\BrApiFreeProvider;
use App\Integrations\BrApiPaidProvider;
use App\Interfaces\MarketDataProviderInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MarketDataProviderInterface::class, function () {
            return match (config('services.brapi.provider')) {
                'paid' => new BrApiPaidProvider(),
                default => new BrApiFreeProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
