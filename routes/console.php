<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Aggregates today's usage every hour to keep real-time dashboards updated
Schedule::command('usage:aggregate')->hourly();

// Aggregates yesterday's usage past midnight to finalize the full day
Schedule::command('usage:aggregate --date=' . now()->subDay()->toDateString())
    ->dailyAt('00:10');

// Runs monthly billing execution on the 1st of every month
Schedule::command('billing:run')->monthlyOn(1, '02:00');
