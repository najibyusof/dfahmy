<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('system:heartbeat')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->onOneServer()
    ->runInBackground();

Schedule::command('housekeeping:notify-overdue')
    ->dailyAt('07:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->runInBackground();

Schedule::command('bookings:send-upcoming-checkin-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->runInBackground();

Schedule::command('bookings:send-outstanding-balance-telegram-alerts')
    ->dailyAt('10:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->runInBackground();
