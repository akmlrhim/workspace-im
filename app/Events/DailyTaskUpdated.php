<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Di-queue (bukan ShouldBroadcastNow) supaya request user tidak menunggu
 * HTTP call ke Pusher selesai. Membutuhkan queue worker yang berjalan.
 */
class DailyTaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $taskListId,
        public int $triggeredBy,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('task-list.'.$this->taskListId)];
    }
}
