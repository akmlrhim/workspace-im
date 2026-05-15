<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyTaskUpdated implements ShouldBroadcastNow
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public function __construct(
		public int $taskListId,
		public int $triggeredBy,
	) {}

	public function broadcastOn(): array
	{
		return [new Channel('task-list.' . $this->taskListId)];
	}
}
