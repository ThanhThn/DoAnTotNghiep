<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

$schedule = app(Schedule::class);


$schedule->command('rental:check-payments')->dailyAt('08:00');
$schedule->command('service:check-payment')->dailyAt('08:05');
$schedule->command('dashboard:change-total')->daily();
$schedule->command('contract:check-overdue')->daily();
$schedule->command('flush:user-channel-state')->everyFiveMinutes();
