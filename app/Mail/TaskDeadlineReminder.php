<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskDeadlineReminder extends Mailable
{
    use SerializesModels;

    public string $urgency;

    public function __construct(
        public Task $task,
        public User $recipient,
    ) {
        $this->urgency = $task->due_date?->isToday() ? 'today' : 'tomorrow';
    }

    public function envelope(): Envelope
    {
        $subject = $this->urgency === 'today'
            ? '[Deadline Hari Ini] '.$this->task->title
            : '[Pengingat Deadline] '.$this->task->title;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.task-deadline-reminder');
    }
}
