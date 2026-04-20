<?php

use Illuminate\Support\Facades\Broadcast;

// Public channels — any authenticated user can listen
// Authorization is handled at the Livewire component level
// Used by: SpaceUpdated, TaskUpdatedGlobal
Broadcast::channel('task-list.{taskListId}', fn () => true);
Broadcast::channel('workspace.{workspaceId}', fn () => true);
Broadcast::channel('task.{taskId}', fn () => true);
