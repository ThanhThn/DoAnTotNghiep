<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;


Artisan::command('inspire', function () {
   \Illuminate\Support\Facades\Log::info("RUN TEST: ", [\Carbon\Carbon::now()]);
})->everyMinute();

$schedule = app(Schedule::class);


$schedule->command('rental:check-payments')->dailyAt('08:00');
$schedule->command('service:check-payment')->dailyAt('08:05');
$schedule->command('dashboard:change-total')->daily();
