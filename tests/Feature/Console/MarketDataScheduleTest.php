<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\Event;
use Tests\TestCase;

class MarketDataScheduleTest extends TestCase
{
    public function test_market_data_fetch_is_scheduled()
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make(Schedule::class);

        $events = collect($schedule->events());

        // Find the specific command
        $event = $events->first(function (Event $event) {
            return str_contains($event->command, 'market-data:fetch');
        });

        $this->assertNotNull($event, 'The command market-data:fetch is not scheduled.');
        $this->assertEquals('*/30 * * * 1-5', $event->expression);
        $this->assertEquals('America/Sao_Paulo', $event->timezone);
        $this->assertTrue($event->withoutOverlapping);
    }
}
