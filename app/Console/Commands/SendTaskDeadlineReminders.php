<?php

namespace App\Console\Commands;

use App\Mail\TaskDeadlineReminder;
use App\Models\Project\Task;
use App\Models\Project\TaskDeadlineNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('tasks:send-deadline-reminders {--test : Bypass all checks, resend to all assignees of due tasks} {--to= : Override recipient email (--test only)}')]
#[Description('Send email reminders to assignees for tasks due today or tomorrow')]
class SendTaskDeadlineReminders extends Command
{
    public function handle(): int
    {
        $isTest = $this->option('test');
        $overrideTo = $this->option('to');

        $targets = [today(), today()->addDay()];

        $query = Task::with(['assignees', 'taskList.space', 'status', 'labels'])
            ->whereNotNull('due_date')
            ->whereIn('due_date', $targets)
            ->whereNull('parent_id')
            ->whereHas('assignees');

        // Skip closed tasks unless running in test mode
        if (! $isTest) {
            $query->whereDoesntHave('status', fn ($q) => $q->where('type', 'closed'));
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks require reminders today.');

            return self::SUCCESS;
        }

        $this->info("Found {$tasks->count()} task(s) due today/tomorrow.");

        $sent = 0;
        $skipped = 0;

        foreach ($tasks as $task) {
            $this->line("  Task: [{$task->due_date->toDateString()}] {$task->title}");

            foreach ($task->assignees as $user) {
                // Skip users without an email address
                if (! $user->email) {
                    $this->warn("    ⚠ Skip {$user->name} — no email address.");
                    $skipped++;

                    continue;
                }

                // In test mode: bypass email verification and already-sent checks
                if (! $isTest) {
                    if (! $user->hasVerifiedEmail()) {
                        $this->warn("    ⚠ Skip {$user->email} — email not verified.");
                        $skipped++;

                        continue;
                    }

                    $record = TaskDeadlineNotification::firstOrCreate([
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                        'due_date' => $task->due_date->toDateString(),
                    ]);

                    if (! $record->wasRecentlyCreated) {
                        $this->line("    – Skip {$user->email} — already notified.");
                        $skipped++;

                        continue;
                    }
                }

                $recipient = $overrideTo ? $user->replicate()->setAttribute('email', $overrideTo) : $user;

                try {
                    Mail::to($recipient)->send(new TaskDeadlineReminder($task, $user));
                    $this->info("    ✓ Sent → {$recipient->email}");
                    $sent++;
                } catch (\Throwable $e) {
                    $this->error("    ✗ Failed → {$recipient->email}: {$e->getMessage()}");
                    $skipped++;
                }
            }
        }

        $this->newLine();
        $this->info("Done. Sent: {$sent}, Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
