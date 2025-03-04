<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;


$schedule = app(Schedule::class);

$schedule->command('queue:work --daemon')->everyMinute()->withoutOverlapping();
