<?php

namespace App\Console\Commands;

use App\Mail\DailyTaskReminder;
use App\Models\DailyTask;
use App\Models\TaskList;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

#[Signature('daily-tasks:send-reminders {--test : Bypass duplicate checks, resend to all} {--to= : Override recipient email (--test only)}')]
#[Description('Send email reminders to list members who have incomplete daily tasks today')]
class SendDailyTaskReminders extends Command
{
    public function handle(): int
    {
        $isTest = $this->option('test');
        $overrideTo = $this->option('to');

        $today = today()->toDateString();

        $taskLists = TaskList::whereHas('dailyTasks', fn ($q) => $q->where('is_active', true)->where('date', $today))
            ->with([
                'space',
                'members',
                'dailyTasks' => fn ($q) => $q->where('is_active', true)->where('date', $today)->orderBy('position'),
                'dailyTasks.logs' => fn ($q) => $q->where('date', $today),
            ])
            ->get();

        if ($taskLists->isEmpty()) {
            $this->info('No task lists with active daily tasks found.');

            return self::SUCCESS;
        }

        $this->info("Found {$taskLists->count()} list(s) with daily tasks.");

        $sent = 0;
        $skipped = 0;

        foreach ($taskLists as $taskList) {
            $this->line("  List: {$taskList->name}");

            foreach ($taskList->members as $user) {
                if (! $user->email) {
                    $this->warn("    ⚠ Skip {$user->name} — no email.");
                    $skipped++;

                    continue;
                }

                if (! $isTest && ! $user->hasVerifiedEmail()) {
                    $this->warn("    ⚠ Skip {$user->email} — email not verified.");
                    $skipped++;

                    continue;
                }

                $pendingTasks = $taskList->dailyTasks->filter(function (DailyTask $dt) use ($user) {
                    $log = $dt->logs->firstWhere('user_id', $user->id);

                    return ! ($log && $log->is_completed);
                });

                if ($pendingTasks->isEmpty()) {
                    $this->line("    – Skip {$user->email} — all daily tasks completed.");
                    $skipped++;

                    continue;
                }

                $recipient = $overrideTo
                    ? $user->replicate()->setAttribute('email', $overrideTo)
                    : $user;

                try {
                    Mail::to($recipient)->send(new DailyTaskReminder($user, $taskList, $pendingTasks));
                    $this->info("    ✓ Sent → {$recipient->email} ({$pendingTasks->count()} pending)");
                    $sent++;
                } catch (\Throwable $e) {
                    $this->error("    ✗ Failed → {$recipient->email}: {$e->getMessage()}");
                    Log::error('Daily task reminder failed', [
                        'task_list_id' => $taskList->id,
                        'user_id' => $user->id,
                        'email' => $recipient->email,
                        'error' => $e->getMessage(),
                    ]);
                    $skipped++;
                }
            }
        }

        $this->newLine();
        $this->info("Done. Sent: {$sent}, Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
