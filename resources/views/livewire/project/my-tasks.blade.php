<div>
  {{-- Header & Filters --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">My Tasks</flux:heading>
      <flux:subheading>All tasks assigned to you across all spaces</flux:subheading>
    </div>

    <div class="flex items-center">
      <flux:select wire:model.live="filterPriority" size="sm" class="w-full sm:w-40">
        <option value="">All Priorities</option>
        <option value="urgent">🔴 Urgent</option>
        <option value="high">🟠 High</option>
        <option value="normal">🔵 Normal</option>
        <option value="low">⚪ Low</option>
      </flux:select>
    </div>
  </div>

  {{-- Tasks List --}}
  <div class="space-y-3">
    @forelse ($tasks as $task)
      <div wire:key="my-task-{{ $task->id }}"
        class="group flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 transition duration-200 hover:border-indigo-200 hover:bg-zinc-50/50 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-indigo-500/30 dark:hover:bg-zinc-800/50">

        {{-- Priority indicator --}}
        <div class="h-10 w-1.5 shrink-0 rounded-full"
          style="background-color: {{ $task->priority_color ?? '#e4e4e7' }}"></div>

        {{-- Task info --}}
        <div class="min-w-0 flex-1">
          <button wire:click="openTaskDetail({{ $task->id }})"
            class="block w-full truncate text-left text-sm font-semibold text-zinc-900 transition-colors hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400">
            {{ $task->title }}
          </button>

          <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
            <span class="flex items-center gap-1 font-medium"
              style="color: {{ $task->taskList->space->color ?? 'inherit' }}">
              {{ $task->taskList->space->name ?? 'No Space' }}
            </span>
            <span class="text-zinc-300 dark:text-zinc-600">&bull;</span>
            <span class="truncate">{{ $task->taskList->name ?? 'Unassigned List' }}</span>
          </div>
        </div>

        {{-- Badges & Meta --}}
        <div class="flex shrink-0 items-center gap-4">
          {{-- Status chip --}}
          <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
            style="background-color: {{ $task->status->color ?? '#6b7280' }}15; color: {{ $task->status->color ?? '#6b7280' }}">
            {{ $task->status->name ?? 'Unknown' }}
          </span>

          {{-- Due date --}}
          @if ($task->due_date)
            <div class="flex w-16 flex-col items-end justify-center">
              <span
                class="text-xs font-medium {{ $task->due_date->isPast() ? 'text-red-600 dark:text-red-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                {{ $task->due_date->format('M d') }}
              </span>
            </div>
          @endif
        </div>
      </div>
    @empty
      {{-- Empty State --}}
      <div
        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 py-16 dark:border-zinc-700 dark:bg-zinc-800/20">
        <div class="mb-4 rounded-full bg-green-100 p-3 dark:bg-green-500/20">
          <flux:icon name="check-circle" class="size-8 text-green-600 dark:text-green-400" />
        </div>
        <flux:heading size="lg">All caught up!</flux:heading>
        <flux:subheading class="mt-1">You have no tasks assigned to you right now.</flux:subheading>
      </div>
    @endforelse
  </div>

  {{-- Task Detail Slideover --}}
  @if ($showTaskDetail && $selectedTaskId)
    <flux:modal wire:model="showTaskDetail" variant="flyout" class="w-full max-w-2xl space-y-0 p-0">
      <livewire:project.task-detail :taskId="$selectedTaskId" :key="'my-detail-' . $selectedTaskId" />
    </flux:modal>
  @endif
</div>
