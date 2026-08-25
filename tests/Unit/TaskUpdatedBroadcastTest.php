<?php

use App\Events\TaskUpdated;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

test('task update broadcasts once across task list and workspace channels', function () {
    $event = new TaskUpdated(taskId: 10, triggeredBy: 5, taskListId: 20, workspaceId: 30);

    $channels = collect($event->broadcastOn())
        ->map(fn ($channel) => (string) $channel)
        ->all();

    expect($event)->toBeInstanceOf(ShouldBroadcastNow::class)
        ->and($channels)->toBe([
            'task.10',
            'task-list.20',
            'workspace.30',
        ]);
});

test('task update only broadcasts available scoped channels', function () {
    $event = new TaskUpdated(taskId: 10, triggeredBy: 5);

    $channels = collect($event->broadcastOn())
        ->map(fn ($channel) => (string) $channel)
        ->all();

    expect($channels)->toBe(['task.10']);
});
