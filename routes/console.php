<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run the low stock check automatically every morning at 8:00 AM
Schedule::command('app:check-low-stock')->dailyAt('08:00');

// Send the morning production briefing automatically at 7:30 AM
Schedule::command('app:send-daily-briefing')->dailyAt('07:30');

// Send the daily stock usage report automatically at 8:00 AM
Schedule::command('app:send-daily-stock-usage')->dailyAt('08:00');
