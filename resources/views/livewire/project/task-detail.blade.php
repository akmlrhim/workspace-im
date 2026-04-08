@php $ro = !$canManage; @endphp
<div class="space-y-6">
  @if ($task)
    @if ($ro)
      <div
        class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-400">
        <flux:icon name="lock-closed" class="size-4 shrink-0" />
        <span>You are viewing this task in <strong>read-only</strong> mode. You are not assigned to this task.</span>
      </div>
    @endif
    <div class="space-y-4">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <flux:input wire:model.blur="taskTitle" wire:change="saveTitle" :readonly="$ro" class="text-xl font-bold"
            placeholder="Task title..." />
        </div>
        @if ($canManage)
          <div class="ml-4 flex shrink-0 items-center">
            <flux:button icon="trash" size="sm" variant="ghost"
              class="text-red-500 hover:bg-red-50 hover:text-red-600"
              wire:click="$dispatch('open-delete-task-modal', { taskId: {{ $task->id }} })" title="Delete Task" />
          </div>
        @endif
      </div>

      <div class="flex flex-wrap gap-3">
        <div class="w-auto min-w-[140px]">
          <flux:select wire:model.live="taskStatusId" wire:change="updateStatus($event.target.value)"
            :disabled="$ro">
            @foreach ($statuses as $status)
              <flux:select.option value="{{ $status->id }}">{{ $status->name }}</flux:select.option>
            @endforeach
          </flux:select>
        </div>

        <div class="w-auto min-w-[140px]">
          <flux:select wire:model.live="taskPriority" wire:change="updatePriority($event.target.value)"
            :disabled="$ro">
            <flux:select.option value="urgent">🔴 Urgent</flux:select.option>
            <flux:select.option value="high">🟠 High</flux:select.option>
            <flux:select.option value="normal">🔵 Normal</flux:select.option>
            <flux:select.option value="low">⚪ Low</flux:select.option>
          </flux:select>
        </div>

        <div class="w-auto min-w-[150px]">
          <flux:input type="date" wire:model="taskDueDate" wire:change="updateDueDate" :readonly="$ro" />
        </div>
      </div>

      <div>
        <div class="mb-2 flex items-center justify-between">
          <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Assignees</span>
        </div>

        <div class="mb-2 flex flex-wrap gap-1.5">
          @forelse ($task->assignees as $assignee)
            <span
              class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
              <flux:avatar :name="$assignee->name" :initials="$assignee->initials()" size="xs" />
              {{ $assignee->name }}
            </span>
          @empty
            <span class="text-xs text-zinc-400">No assignees</span>
          @endforelse
        </div>

        <div
          class="max-h-32 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700 {{ $ro ? 'opacity-60' : '' }}">
          @foreach ($workspaceUsers as $member)
            <label
              class="flex items-center gap-2 rounded-md px-2 py-1 text-sm {{ $canManage ? 'hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer' : 'cursor-not-allowed' }}">
              <flux:checkbox wire:model="taskAssigneeIds" :value="$member->id" wire:change="updateAssignees"
                :disabled="$ro" />
              <flux:avatar :name="$member->name" :initials="$member->initials()" size="xs" />
              <span class="text-zinc-700 dark:text-zinc-300">{{ $member->name }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <div>
        <div class="mb-2 flex items-center justify-between">
          <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Labels</span>
          @if ($canManage)
            <flux:button icon="plus" size="xs" variant="ghost" wire:click="$toggle('showLabelForm')">Buat Baru
            </flux:button>
          @endif
        </div>

        <div class="mb-2 flex flex-wrap gap-1.5">
          @forelse ($task->labels as $label)
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-medium text-white"
              style="background-color: {{ $label->color }}">
              {{ $label->name }}
              @if ($canManage)
                <button wire:click="toggleLabel({{ $label->id }})" class="ml-0.5 opacity-70 hover:opacity-100"
                  title="Remove label">
                  <flux:icon name="x-mark" class="size-3" />
                </button>
              @endif
            </span>
          @empty
            <span class="text-xs text-zinc-400">No labels</span>
          @endforelse
        </div>

        @if ($canManage && $allLabels->isNotEmpty())
          <div class="max-h-28 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700">
            @foreach ($allLabels as $label)
              <button wire:click="toggleLabel({{ $label->id }})"
                class="flex w-full items-center gap-2 rounded-md px-2 py-1 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                <div
                  class="flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 transition-colors
                  {{ $task->labels->contains('id', $label->id) ? 'border-indigo-500 bg-indigo-500' : 'border-zinc-300 dark:border-zinc-600' }}">
                  @if ($task->labels->contains('id', $label->id))
                    <flux:icon name="check" class="size-2.5 text-white" />
                  @endif
                </div>
                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $label->color }}"></span>
                <span class="text-zinc-700 dark:text-zinc-300">{{ $label->name }}</span>
              </button>
            @endforeach
          </div>
        @endif

        @if ($canManage && $showLabelForm)
          <form wire:submit="createLabel"
            class="mt-2 space-y-2 rounded-lg border border-indigo-200 bg-indigo-50/50 p-2.5 dark:border-indigo-800/40 dark:bg-indigo-900/10">
            <flux:input wire:model="newLabelName" placeholder="Nama label..." size="sm" autofocus />
            <div class="flex items-center gap-2">
              <span class="text-xs text-zinc-500 dark:text-zinc-400">Warna:</span>
              <div class="flex gap-1">
                @foreach (['#6366f1', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6b7280'] as $color)
                  <button type="button" wire:click="$set('newLabelColor', '{{ $color }}')"
                    class="h-4 w-4 rounded-full border-2 transition-transform hover:scale-125 {{ $newLabelColor === $color ? 'border-zinc-900 dark:border-white scale-125' : 'border-transparent' }}"
                    style="background-color: {{ $color }}"></button>
                @endforeach
              </div>
            </div>
            <div class="flex justify-end gap-1">
              <flux:button size="xs" variant="ghost" wire:click="$set('showLabelForm', false)">Batal</flux:button>
              <flux:button size="xs" variant="primary" type="submit">Buat Label</flux:button>
            </div>
          </form>
        @endif
      </div>
    </div>

    <flux:separator />

    <div>
      <h3 class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Description</h3>
      <flux:textarea wire:model.blur="taskDescription" wire:change="saveDescription" :readonly="$ro"
        rows="4" placeholder="{{ $canManage ? 'Add a detailed description...' : 'No description.' }}" />
    </div>

    <flux:separator />

    <div>
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
          Checklist
          @if ($subtasks->isNotEmpty())
            @php
              $completedCount = $subtasks->where('is_completed', true)->count();
              $totalCount = $subtasks->count();
            @endphp
            <span class="ml-1 text-zinc-400">({{ $completedCount }}/{{ $totalCount }})</span>
          @endif
        </h3>
        @if ($canManage)
          <flux:button icon="plus" size="xs" variant="ghost" wire:click="$toggle('showSubtaskForm')">Add
          </flux:button>
        @endif
      </div>

      @if ($subtasks->isNotEmpty())
        @php $progress = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0; @endphp
        <div class="mb-3 h-1.5 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
          <div
            class="h-full rounded-full transition-all duration-300 {{ $progress == 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
            style="width: {{ $progress }}%"></div>
        </div>
      @endif

      @if ($canManage && $showSubtaskForm)
        <form wire:submit="addSubtask" class="mb-3 flex gap-2">
          <flux:input wire:model="newSubtaskTitle" placeholder="Subtask title..." size="sm" class="flex-1"
            autofocus />
          <flux:button type="submit" size="sm" variant="primary">Add</flux:button>
        </form>
      @endif

      <div class="space-y-0.5" x-data="{ editingId: null, editTitle: '' }">
        @foreach ($subtasks as $subtask)
          <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 group"
            wire:key="subtask-{{ $subtask->id }}">
            <button
              @if ($canManage) wire:click="toggleSubtaskComplete({{ $subtask->id }})" @else disabled @endif
              class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition-colors
                {{ $subtask->is_completed ? 'border-green-500 bg-green-500 text-white' : 'border-zinc-300 dark:border-zinc-600' }}
                {{ $canManage ? 'hover:border-indigo-400' : 'cursor-not-allowed opacity-60' }}">
              @if ($subtask->is_completed)
                <flux:icon name="check" class="size-3.5 text-white stroke-[3]" />
              @endif
            </button>

            <span x-show="editingId !== {{ $subtask->id }}"
              @if ($canManage) x-on:dblclick="editingId = {{ $subtask->id }}; editTitle = '{{ addslashes($subtask->title) }}'; $nextTick(() => $refs['edit_subtask_{{ $subtask->id }}'].focus())" @endif
              class="flex-1 text-sm {{ $subtask->is_completed ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-700 dark:text-zinc-300' }} {{ $canManage ? 'cursor-text cursor-pointer' : '' }}">
              {{ $subtask->title }}
            </span>

            <div x-show="editingId === {{ $subtask->id }}" x-cloak class="flex-1">
              <flux:input x-ref="edit_subtask_{{ $subtask->id }}" x-model="editTitle"
                x-on:keydown.enter="$wire.editSubtaskTitle({{ $subtask->id }}, editTitle); editingId = null"
                x-on:keydown.escape="editingId = null" x-on:blur="editingId = null" size="sm" />
            </div>

            @if ($canManage)
              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button type="button"
                  x-on:click="editingId = {{ $subtask->id }}; editTitle = '{{ addslashes($subtask->title) }}'; $nextTick(() => $refs['edit_subtask_{{ $subtask->id }}'].focus())"
                  class="text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                  <flux:icon name="pencil" class="size-3.5" />
                </button>
                <button type="button" wire:click="deleteSubtask({{ $subtask->id }})"
                  class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400">
                  <flux:icon name="trash" class="size-3.5" />
                </button>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>

    <flux:separator />

    <div>
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Time Tracking</h3>
        <span class="text-xs text-zinc-500 dark:text-zinc-400">
          Total: {{ floor($totalTimeSeconds / 3600) }}h {{ floor(($totalTimeSeconds % 3600) / 60) }}m
        </span>
      </div>

      @if ($canManage)
        @if ($activeTimerId)
          <flux:button icon="stop-circle" variant="danger" size="sm" wire:click="stopTimer" class="w-full">
            Stop Timer</flux:button>
        @else
          <flux:button icon="play-circle" variant="primary" size="sm" wire:click="startTimer" class="w-full">
            Start Timer</flux:button>
        @endif
      @else
        <div
          class="flex items-center justify-center rounded-lg border border-dashed border-zinc-200 py-3 text-xs text-zinc-400 dark:border-zinc-700">
          <flux:icon name="lock-closed" class="mr-1.5 size-3.5" />
          Time tracking not available in read-only mode
        </div>
      @endif

      @if ($timeEntries->isNotEmpty())
        <div class="mt-3 space-y-1">
          @foreach ($timeEntries as $entry)
            <div
              class="flex items-center justify-between rounded-md px-3 py-1.5 text-xs text-zinc-500 dark:text-zinc-400">
              <span>{{ $entry->started_at->format('M d, H:i') }}</span>
              <span>
                @if ($entry->stopped_at)
                  {{ floor($entry->duration_seconds / 3600) }}h {{ floor(($entry->duration_seconds % 3600) / 60) }}m
                @else
                  <span class="animate-pulse text-green-500">● Running</span>
                @endif
              </span>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <flux:separator />

    <div>
      <h3 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
        Attachments
        @if ($attachments->isNotEmpty())
          <span class="ml-1 text-zinc-400">({{ $attachments->count() }})</span>
        @endif
      </h3>

      @if ($canManage)
        <div class="mb-3">
          <label
            class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-500 transition-colors hover:border-zinc-400 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-zinc-500 dark:hover:text-zinc-300">
            <flux:icon name="cloud-arrow-up" class="size-5" />
            <span>Upload file</span>
            <input type="file" wire:model="uploadFile" class="hidden" />
          </label>
          @if ($uploadFile)
            <div class="mt-2 flex items-center gap-2">
              <span class="text-xs text-zinc-500">{{ $uploadFile->getClientOriginalName() }}</span>
              <flux:button size="xs" variant="primary" wire:click="uploadAttachment">Upload</flux:button>
            </div>
          @endif
        </div>
      @endif

      @forelse ($attachments as $attachment)
        <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
          wire:key="attach-{{ $attachment->id }}">
          <div class="flex items-center gap-2">
            <flux:icon name="document" class="size-4 text-zinc-400" />
            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $attachment->filename }}</span>
            <span class="text-xs text-zinc-400">{{ number_format($attachment->size / 1024, 1) }} KB</span>
          </div>
          @if ($canManage)
            <flux:button icon="trash" size="xs" variant="ghost"
              wire:click="deleteAttachment({{ $attachment->id }})" class="text-red-500" />
          @endif
        </div>
      @empty
        <p class="text-xs text-zinc-400">No attachments yet.</p>
      @endforelse
    </div>

    <flux:separator />

    <div>
      <h3 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
        Comments
        @if ($comments->isNotEmpty())
          <span class="ml-1 text-zinc-400">({{ $comments->count() }})</span>
        @endif
      </h3>

      <form wire:submit="addComment" class="mb-4">
        <flux:textarea wire:model="newComment" placeholder="Write a comment..." rows="2" />
        <div class="mt-2 flex justify-end">
          <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane">Send</flux:button>
        </div>
      </form>

      <div class="space-y-4">
        @foreach ($comments as $comment)
          <div class="rounded-lg border border-zinc-100 p-3 dark:border-zinc-700"
            wire:key="comment-{{ $comment->id }}">
            <div class="mb-2 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <flux:avatar :name="$comment->user->name" :initials="$comment->user->initials()" size="xs" />
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $comment->user->name }}</span>
                <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
              </div>
              @if ($comment->user_id === auth()->id())
                <flux:button icon="trash" size="xs" variant="ghost"
                  wire:click="deleteComment({{ $comment->id }})" class="text-red-500" />
              @endif
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $comment->body }}</p>

            @if ($comment->replies->isNotEmpty())
              <div class="mt-3 ml-6 space-y-3 border-l-2 border-zinc-200 pl-3 dark:border-zinc-600">
                @foreach ($comment->replies as $reply)
                  <div wire:key="reply-{{ $reply->id }}">
                    <div class="flex items-center gap-2 mb-1">
                      <flux:avatar :name="$reply->user->name" :initials="$reply->user->initials()" size="xs" />
                      <span
                        class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ $reply->user->name }}</span>
                      <span class="text-[10px] text-zinc-400">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-pre-wrap">{{ $reply->body }}</p>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>

    <flux:separator />

    <div>
      <h3 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Activity</h3>
      <div class="space-y-2">
        @foreach ($activities as $activity)
          <div class="flex items-start gap-2 text-xs" wire:key="activity-{{ $activity->id }}">
            <flux:avatar :name="$activity->user->name" :initials="$activity->user->initials()" size="xs"
              class="mt-0.5" />
            <div>
              <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $activity->user->name }}</span>
              <span class="text-zinc-500 dark:text-zinc-400">
                @switch($activity->type)
                  @case('created')
                    created this task
                  @break

                  @case('status_changed')
                    changed status from <span class="font-medium">{{ $activity->old_value }}</span>
                    to <span class="font-medium">{{ $activity->new_value }}</span>
                  @break

                  @case('priority_changed')
                    changed priority from <span class="font-medium">{{ $activity->old_value }}</span>
                    to <span class="font-medium">{{ $activity->new_value }}</span>
                  @break

                  @case('assignee_changed')
                    assigned to <span class="font-medium">{{ $activity->new_value }}</span>
                  @break

                  @default
                    {{ $activity->type }}
                @endswitch
              </span>
              <span class="block text-zinc-400 dark:text-zinc-500">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="pt-2 text-xs text-zinc-400 dark:text-zinc-500">
      Created by {{ $task->creator?->name ?? 'Unknown' }} · {{ $task->created_at->format('M d, Y \a\t H:i') }}
    </div>
  @else
    <div class="flex items-center justify-center py-12">
      <p class="text-zinc-500">Task not found</p>
    </div>
  @endif
</div>
