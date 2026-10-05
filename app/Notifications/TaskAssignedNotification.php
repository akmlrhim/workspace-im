<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public string $assignedBy) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Task baru ditugaskan',
            'body' => "{$this->assignedBy} menugaskan Anda ke task: {$this->task->title}",
            'task_id' => $this->task->id,
            'task_list_id' => $this->task->task_list_id,
        ];
    }
}
