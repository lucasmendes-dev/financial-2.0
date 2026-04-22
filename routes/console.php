<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('market-data:fetch')
    ->weekdays()
    ->everyFifteenMinutes()
    ->between('10:15', '17:15')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Market data fetch failed');
    });
