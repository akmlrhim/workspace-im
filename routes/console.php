<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Tidak ada queue worker di sini dengan sengaja. Broadcast dikirim sinkron
 * (lihat app/Events/TaskUpdated.php) dan tidak ada ShouldQueue lain di
 * aplikasi ini, jadi cron `schedule:run` hanya perlu melayani dua reminder
 * di bawah. Kalau nanti ada job yang benar-benar di-queue, barulah worker
 * perlu ditambahkan — dan shared hosting butuh `--max-time`, bukan
 * `--stop-when-empty`, agar update tidak tertunda sampai satu menit.
 */

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
