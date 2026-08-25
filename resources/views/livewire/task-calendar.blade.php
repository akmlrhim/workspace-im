<div>
  <div class="mb-6">
    @include('livewire.partials.breadcrumb')
    <div class="flex items-center justify-between mb-4">
      <h1 class="hidden text-2xl font-bold text-zinc-900 dark:text-white lg:block">{{ $taskList->name }}</h1>
    </div>
    @include('livewire.partials.view-toggle', ['active' => 'calendar'])
  </div>

  <div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="previousMonth" />
      <flux:button size="sm" variant="ghost" wire:click="goToToday">Hari Ini</flux:button>
      <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="nextMonth" />
    </div>
    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white sm:text-lg">{{ $monthLabel }}</h2>
  </div>

  <div class="hidden overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 sm:block">
    <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
      @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
        <div
          class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400
          {{ in_array($dayName, ['Sat', 'Sun']) ? 'bg-zinc-100/50 dark:bg-zinc-800/30' : '' }}">
          {{ $dayName }}
        </div>
      @endforeach
    </div>

    @foreach ($weeks as $week)
      <div class="grid grid-cols-7 border-b border-zinc-100 last:border-b-0 dark:border-zinc-800">
        @foreach ($week as $day)
          @php $dateStr = $day['date']->format('Y-m-d'); @endphp
          <div
            class="group/cell min-h-[130px] border-r border-zinc-100 p-1.5 last:border-r-0 dark:border-zinc-800
            {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/50 dark:bg-zinc-900/30' : 'bg-white dark:bg-zinc-900' }}
            {{ $day['date']->isWeekend() && !$day['isToday'] ? 'bg-zinc-50 dark:bg-zinc-800/20' : '' }}
            {{ $day['isToday'] ? 'border-t-2 border-t-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : '' }}"
            wire:key="cal-{{ $dateStr }}">

            <div class="mb-1 flex items-center justify-between">
              <span
                class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold
                {{ $day['isToday'] ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300 dark:shadow-indigo-900' : '' }}
                {{ !$day['isCurrentMonth'] && !$day['isToday'] ? 'text-zinc-300 dark:text-zinc-600' : (!$day['isToday'] ? 'text-zinc-700 dark:text-zinc-300' : '') }}">
                {{ $day['date']->day }}
              </span>

              @if ($day['isCurrentMonth'])
                <button @click="$flux.modal('cal-create-task').show(); $wire.openCreateTask('{{ $dateStr }}')"
                  class="cursor-pointer flex h-6 w-6 items-center justify-center rounded-md transition-all duration-150 bg-indigo-50 text-indigo-500 dark:bg-gray-900/30 dark:text-indigo-400 hover:bg-indigo-500 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white"
                  title="Tambah tugas pada {{ $day['date']->isoFormat('D MMM') }}">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                </button>
              @endif
            </div>

            <div class="space-y-0.5">
              @foreach ($day['tasks']->take(3) as $task)
                <div class="group/task flex w-full">
                  @php
                    $calDone = $task->status?->type === 'closed';
                    $calOver = !$calDone && $task->due_date?->isPast();
                  @endphp
                  <button
                    @click="$flux.modal('task-detail-calendar').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                    class="flex w-full items-center gap-1 rounded-md px-1.5 py-1 text-left text-[11px] font-medium transition-all duration-150 hover:-translate-y-px hover:shadow-sm {{ $calOver ? 'ring-1 ring-red-300/60 dark:ring-red-500/30' : '' }}"
                    style="background-color: {{ $task->status->color ?? '#6366f1' }}{{ $calOver ? '20' : '15' }}; color: {{ $task->status->color ?? '#6366f1' }};"
                    title="{{ $task->title }}">
                    @if ($calOver)
                      <span class="relative flex h-1.5 w-1.5 shrink-0">
                        <span
                          class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-60"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-red-500"></span>
                      </span>
                    @else
                      <div class="h-1.5 w-1.5 shrink-0 rounded-full"
                        style="background-color: {{ $calDone ? '#22c55e' : $task->priority_color }}"></div>
                    @endif
                    <span class="min-w-0 flex-1 truncate">{{ $task->title }}</span>
                    @if ($calOver)
                      <span
                        class="ml-auto shrink-0 rounded px-1 py-0.5 text-[9px] font-bold bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">
                        {{ $task->due_date->format('d M') }}
                      </span>
                    @elseif ($calDone)
                      <span
                        class="ml-auto shrink-0 rounded px-1 py-0.5 text-[9px] font-bold bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">
                        {{ $task->due_date->format('d M') }}
                      </span>
                    @endif
                  </button>
                </div>
              @endforeach

              @if ($day['tasks']->count() > 3)
                <span class="block px-1.5 text-[10px] text-zinc-400 dark:text-zinc-500">
                  +{{ $day['tasks']->count() - 3 }} lagi
                </span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>

  <div class="space-y-1 sm:hidden">
    @foreach ($weeks as $week)
      @foreach ($week as $day)
        @if ($day['isCurrentMonth'])
          @php
            $hasTasks = $day['tasks']->isNotEmpty();
            $dateStr = $day['date']->format('Y-m-d');
          @endphp
          <div
            class="rounded-xl border px-3 py-2.5 transition-colors
            {{ $day['isToday'] ? 'border-indigo-400 bg-indigo-50 ring-1 ring-indigo-300 dark:border-indigo-600 dark:bg-indigo-950/40 dark:ring-indigo-700' : 'border-zinc-100 dark:border-zinc-800' }}"
            wire:key="cal-m-{{ $dateStr }}">

            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span
                  class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold
                  {{ $day['isToday'] ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300 dark:shadow-indigo-900' : 'text-zinc-700 dark:text-zinc-300' }}">
                  {{ $day['date']->day }}
                </span>
                <span
                  class="text-xs font-medium {{ $day['isToday'] ? 'font-bold text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                  {{ $day['date']->format('D') }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                @if ($hasTasks)
                  <span
                    class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    {{ $day['tasks']->count() }}
                  </span>
                @endif
                <button @click="$flux.modal('cal-create-task').show(); $wire.openCreateTask('{{ $dateStr }}')"
                  class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-50 text-indigo-500 transition-colors hover:bg-indigo-500 hover:text-white active:bg-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-500 dark:hover:text-white"
                  title="Tambah tugas pada {{ $day['date']->isoFormat('D MMM') }}">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                </button>
              </div>
            </div>

            @if ($hasTasks)
              <div class="mt-2 space-y-1 pl-9">
                @foreach ($day['tasks'] as $task)
                  <button
                    @click="$flux.modal('task-detail-calendar').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                    class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs font-medium transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
                    @php
                      $calDone = $task->status?->type === 'closed';
                      $calOver = !$calDone && $task->due_date?->isPast();
                    @endphp
                    <div class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $task->priority_color }}">
                    </div>
                    <span class="flex-1 truncate text-zinc-900 dark:text-zinc-100">{{ $task->title }}</span>
                    @if ($calOver)
                      <span
                        class="ml-auto shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">
                        {{ $task->due_date->isoFormat('D MMM') }}
                      </span>
                    @elseif ($calDone)
                      <span
                        class="ml-auto shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">
                        {{ $task->due_date->isoFormat('D MMM') }}
                      </span>
                    @endif
                    @if ($task->assignee)
                      <flux:avatar circle :name="$task->assignee->name" :initials="$task->assignee->initials()"
                        :src="$task->assignee->avatar" size="xs" class="ml-auto shrink-0" />
                    @endif
                  </button>
                @endforeach
              </div>
            @endif
          </div>
        @endif
      @endforeach
    @endforeach
  </div>

  @livewire('task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('task-delete-modal')

  <flux:modal name="cal-create-task" wire:model="showCreateTask" class="w-full max-w-sm">
    <div class="space-y-5">
      <div>
        <flux:heading size="lg">Tambah Tugas</flux:heading>
        @if ($createTaskDate)
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Tenggat:
            <span class="font-medium text-indigo-600 dark:text-indigo-400">
              {{ \Carbon\Carbon::parse($createTaskDate)->isoFormat('dddd, D MMMM Y') }}
            </span>
          </p>
        @endif
      </div>

      <form wire:submit="storeTask" class="space-y-4">
        <flux:field>
          <flux:label>Nama Tugas</flux:label>
          <flux:input wire:model="newTaskTitle" placeholder="Apa yang perlu dikerjakan?" autofocus />
          <flux:error name="newTaskTitle" />
        </flux:field>

        <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('cal-create-task').close()">
            Batal
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
            Tambah Tugas
          </flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  <x-task-detail-modal name="task-detail-calendar" keyPrefix="cal-detail" :selectedTaskId="$selectedTaskId" />
</div>
