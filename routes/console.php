<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('market-data:fetch')
    ->weekdays()
    ->everyTwoHours()
    ->between('10:00', '18:05')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Market data fetch failed');
    });

Schedule::command('sanctum:prune-expired --hours=24')->daily();
