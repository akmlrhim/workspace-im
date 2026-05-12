<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tasks:send-deadline-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/deadline-reminders.log'))
    ->onFailure(fn () => Log::error('tasks:send-deadline-reminders failed — check '.storage_path('logs/deadline-reminders.log')));

Schedule::command('daily-tasks:send-reminders')
    ->dailyAt('17:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/daily-task-reminders.log'))
    ->onFailure(fn () => Log::error('daily-tasks:send-reminders failed — check '.storage_path('logs/daily-task-reminders.log')));
