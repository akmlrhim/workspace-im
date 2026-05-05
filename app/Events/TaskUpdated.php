<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $taskId,
        public int $triggeredBy,
        public ?int $taskListId = null,
        public ?int $workspaceId = null,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('task.'.$this->taskId),
        ];

        if ($this->taskListId) {
            $channels[] = new Channel('task-list.'.$this->taskListId);
        }

        if ($this->workspaceId) {
            $channels[] = new Channel('workspace.'.$this->workspaceId);
        }

        return $channels;
    }
}
