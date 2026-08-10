<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dikirim sinkron, bukan lewat queue: aplikasi berjalan di shared hosting yang
 * tidak bisa menjaga worker tetap hidup, dan queue yang mati berarti realtime
 * mati diam-diam. Biaya HTTP call ke Pusher dibatasi lewat client_options di
 * config/broadcasting.php, dan kegagalannya ditelan oleh BroadcastsChangesSafely
 * sehingga tidak pernah menggagalkan aksi user.
 */
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
