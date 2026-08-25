<?php

namespace App\Mail;

use App\Models\DailyTask;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyTaskReminder extends Mailable
{
    use SerializesModels;

    /**
     * @param  Collection<int, DailyTask>  $pendingTasks
     */
    public function __construct(
        public User $recipient,
        public TaskList $taskList,
        public Collection $pendingTasks,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Daily Task] Belum selesai: '.$this->taskList->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-task-reminder');
    }
}
