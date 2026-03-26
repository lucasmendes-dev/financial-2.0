<?php

namespace App\Providers;

use App\Integrations\BrApiFreeAdapter;
use App\Integrations\BrApiPaidAdapter;
use App\Interfaces\MarketDataAdapterInterface;

use Illuminate\Support\ServiceProvider; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MarketDataAdapterInterface::class, function () {
            return match (config('services.brapi.provider')) {
                'paid' => new BrApiPaidAdapter(),
                default => new BrApiFreeAdapter(),
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
