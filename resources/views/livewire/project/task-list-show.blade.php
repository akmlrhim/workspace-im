<div>
  <div class="mb-6">
    @include('livewire.project.partials.breadcrumb')

    <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="hidden lg:block lg:text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>

      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
        <div class="w-full sm:w-auto">
          @include('livewire.project.partials.view-toggle', ['active' => 'list'])
        </div>
        <flux:button icon="plus" variant="primary" size="sm" class="w-full justify-center sm:w-auto"
          wire:click="$dispatch('open-create-task-form')">
          Tambah Task
        </flux:button>
      </div>
    </div>
  </div>

  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
    <div class="relative w-full sm:max-w-xs sm:flex-1">
      <flux:input wire:model.live.debounce.300ms="searchQuery" icon="magnifying-glass" placeholder="Search tasks..."
        size="sm" class="w-full" />
    </div>

    <div class="flex w-full gap-2 sm:w-auto">
      <flux:select wire:model.live="filterPriority" size="sm" class="flex-1 sm:w-36">
        <option value="">All Priorities</option>
        <option value="urgent">🔴 Urgent</option>
        <option value="high">🟠 High</option>
        <option value="normal">🔵 Normal</option>
        <option value="low">⚪ Low</option>
      </flux:select>

      <flux:select wire:model.live="filterStatus" size="sm" class="flex-1 sm:w-36">
        <option value="">All Statuses</option>
        @foreach ($this->statuses as $status)
          <option value="{{ $status->id }}">{{ $status->name }}</option>
        @endforeach
      </flux:select>
    </div>
  </div>

  <div class="space-y-6">
    @forelse ($this->statuses as $status)
      @php
        $statusTasks = collect($this->tasks)->where('task_status_id', $status->id);
      @endphp

      @if ($statusTasks->isNotEmpty() || empty($filterStatus))
        <div>
          <div class="mb-3 flex items-center gap-2 px-1">
            <div class="h-3 w-3 rounded-full" style="background-color: {{ $status->color }}"></div>
            <h2 class="text-sm lg:text-lg font-semibold text-zinc-900 dark:text-white">{{ $status->name }}</h2>
            <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
              {{ $statusTasks->count() }}
            </span>
          </div>
          <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">

            <div
              class="hidden border-b border-zinc-200 bg-zinc-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400 lg:flex lg:items-center lg:gap-4">
              <div class="flex-1">Task</div>
              <div class="w-32 shrink-0">Priority</div>
              <div class="w-32 shrink-0">Assignees</div>
              <div class="w-24 shrink-0">Due Date</div>
              <div class="w-10 shrink-0"></div>
            </div>

            @forelse ($statusTasks as $task)
              <div wire:key="task-{{ $task->id }}"
                class="group flex flex-col gap-3 border-b border-zinc-100 px-4 py-3 transition-colors last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/70 lg:flex-row lg:items-center lg:gap-4">
                <div class="flex flex-1 items-start justify-between gap-3 min-w-0 lg:items-center">
                  <div class="flex min-w-0 items-center gap-3">
                    <div class="h-5 w-1 shrink-0 rounded-full" style="background-color: {{ $task->priority_color }}">
                    </div>

                    <button wire:click="openTaskDetail({{ $task->id }})"
                      class="truncate text-left text-sm font-medium text-zinc-900 hover:text-indigo-600 hover:underline dark:text-zinc-100 dark:hover:text-indigo-400">
                      {{ $task->title }}
                    </button>

                    @if ($task->subtasks->isNotEmpty())
                      <span
                        class="shrink-0 rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                        {{ $task->subtasks->count() }} sub
                      </span>
                    @endif
                  </div>

                  <div class="lg:hidden shrink-0">
                    @if ($task->canBeManagedBy(auth()->user()))
                      <div @click.stop>
                        <flux:dropdown position="bottom" align="end">
                          <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                            class="h-8 w-8 p-0 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300" />
                          <flux:menu>
                            <flux:menu.item icon="pencil-square"
                              wire:click="$dispatch('open-edit-task-form', { taskId: {{ $task->id }} })">
                              Edit
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item variant="danger" icon="trash"
                              wire:click="$dispatch('open-delete-task-modal', { taskId: {{ $task->id }} })">
                              Delete
                            </flux:menu.item>
                          </flux:menu>
                        </flux:dropdown>
                      </div>
                    @endif
                  </div>
                </div>

                <div class="flex items-center justify-between gap-3 lg:w-auto lg:justify-start lg:gap-4">

                  <div class="w-auto lg:w-32 lg:shrink-0">
                    <flux:select wire:change="updateTaskPriority({{ $task->id }}, $event.target.value)"
                      size="xs" class="text-xs">
                      <option value="urgent" {{ $task->priority === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                      <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>🟠 High</option>
                      <option value="normal" {{ $task->priority === 'normal' ? 'selected' : '' }}>🔵 Normal</option>
                      <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>⚪ Low</option>
                    </flux:select>
                  </div>

                  <div class="flex flex-1 items-center justify-end gap-3 lg:flex-initial lg:justify-start lg:gap-4">

                    <div class="flex items-center lg:w-32 lg:shrink-0">
                      @if ($task->assignees->isNotEmpty())
                        <div class="flex -space-x-1.5">
                          @foreach ($task->assignees->take(3) as $assignee)
                            <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()"
                              :src="$assignee->avatar" size="xs" class="ring-2 ring-white dark:ring-zinc-900"
                              title="{{ $assignee->name }}" />
                          @endforeach
                          @if ($task->assignees->count() > 3)
                            <div
                              class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-[9px] font-medium text-zinc-600 ring-2 ring-white dark:bg-zinc-700 dark:text-zinc-400 dark:ring-zinc-900">
                              +{{ $task->assignees->count() - 3 }}
                            </div>
                          @endif
                        </div>
                      @elseif ($task->assignee)
                        <div class="flex items-center gap-2">
                          <flux:avatar circle :name="$task->assignee->name" :initials="$task->assignee->initials()"
                            :src="$task->assignee->avatar" size="xs" />
                          <span class="hidden truncate text-xs text-zinc-600 dark:text-zinc-400 sm:inline-block">
                            {{ $task->assignee->name }}
                          </span>
                        </div>
                      @else
                        <span class="text-xs text-zinc-400">—</span>
                      @endif
                    </div>

                    <div class="flex items-center lg:w-24 lg:shrink-0">
                      @if ($task->due_date)
                        <span
                          class="text-xs {{ $task->due_date->isPast() ? 'font-medium text-red-500' : 'text-zinc-500 dark:text-zinc-400' }}">
                          {{ $task->due_date->format('M d') }}
                        </span>
                      @else
                        <span class="text-xs text-zinc-400">—</span>
                      @endif
                    </div>

                  </div>
                </div>

                <div class="hidden lg:flex lg:w-10 lg:shrink-0 lg:justify-end">
                  @if ($task->canBeManagedBy(auth()->user()))
                    <div @click.stop>
                      <flux:dropdown position="bottom" align="end">
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                          class="h-8 w-8 p-0 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300" />
                        <flux:menu>
                          <flux:menu.item icon="pencil-square"
                            wire:click="$dispatch('open-edit-task-form', { taskId: {{ $task->id }} })">
                            Edit
                          </flux:menu.item>
                          <flux:menu.separator />
                          <flux:menu.item variant="danger" icon="trash"
                            wire:click="$dispatch('open-delete-task-modal', { taskId: {{ $task->id }} })">
                            Delete
                          </flux:menu.item>
                        </flux:menu>
                      </flux:dropdown>
                    </div>
                  @endif
                </div>
              </div>
            @empty
              <div class="py-6 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Tidak ada tasks di status ini.</p>
              </div>
            @endforelse
          </div>
        </div>
      @endif
    @empty
      <div
        class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white py-12 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:icon name="clipboard-document-list" class="mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
        <p class="text-sm text-zinc-500 dark:text-zinc-400">No tasks found</p>
        <flux:button icon="plus" size="sm" variant="ghost" class="mt-2"
          wire:click="$dispatch('open-create-task-form')">
          Add a task
        </flux:button>
      </div>
    @endforelse
  </div>

  <button wire:click="$dispatch('open-create-task-form')"
    class="mt-2 flex w-full items-center gap-2 rounded-lg border border-dashed border-zinc-300 px-4 py-2.5 text-sm text-zinc-400 transition-colors hover:border-zinc-400 hover:text-zinc-600 dark:border-zinc-700 dark:hover:border-zinc-500 dark:hover:text-zinc-300">
    <flux:icon name="plus" class="size-4" />
    Add a task...
  </button>

  @livewire('project.task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('project.task-delete-modal')

  @if ($selectedTaskId)
    <flux:modal wire:model="showTaskDetail"
      class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
      <div class="max-h-[85vh] overflow-y-auto pr-1 max-sm:max-h-none max-sm:h-[calc(100dvh-4rem)]">
        <livewire:project.task-detail :taskId="$selectedTaskId" :key="'task-detail-' . $selectedTaskId" />
      </div>
    </flux:modal>
  @endif
</div>
