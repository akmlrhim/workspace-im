<div>
  {{-- ─── Header ──────────────────────────────────────────── --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">General Taskboard</flux:heading>
      <flux:subheading>{{ $totalLists }} list tersedia di seluruh space</flux:subheading>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      @if ($activeTab === 'lists')
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari list..." size="sm"
          icon="magnifying-glass" class="w-full sm:w-48" />
      @endif

      @if (auth()->user()->canManageLists())
        <flux:button size="sm" variant="ghost" icon="plus"
          @click="$flux.modal('create-list-modal').show(); $wire.openCreateList()">
          Tambah List
        </flux:button>

        <flux:button size="sm" variant="primary" icon="plus"
          @click="$flux.modal('create-space-modal').show(); $wire.set('showCreateSpace', true)">
          Tambah Space
        </flux:button>
      @endif
    </div>
  </div>

  <div class="mb-6 flex items-center gap-1 border-b border-zinc-200 dark:border-zinc-700">
    <button wire:click="$set('activeTab', 'lists')"
      class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
        {{ $activeTab === 'lists'
            ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="squares-2x2" class="size-4" />
      Lists
      <span
        class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ $activeTab === 'lists' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
        {{ $totalLists }}
      </span>
    </button>

    <button wire:click="$set('activeTab', 'calendar')"
      class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
        {{ $activeTab === 'calendar'
            ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="calendar-days" class="size-4" />
      Kalender Deadline
    </button>
  </div>

  @if ($activeTab === 'lists')
    @if ($grouped->isNotEmpty())
      <div class="space-y-8">
        @foreach ($grouped as $spaceName => $lists)
          @php $space = $lists->first()->space; @endphp
          <div>
            <div class="mb-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                @if ($space)
                  <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md"
                    style="background-color: {{ $space->color }}20">
                    <flux:icon name="{{ $space->icon }}" class="size-4" style="color: {{ $space->color }}" />
                  </div>
                @endif
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                  {{ $spaceName }}
                </h2>
                <span
                  class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                  {{ $lists->count() }}
                </span>
              </div>

              @if ($space && auth()->user()->canManageLists())
                <button @click="$flux.modal('create-list-modal').show(); $wire.openCreateList({{ $space->id }})"
                  class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-zinc-400 transition hover:bg-zinc-100 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400">
                  <flux:icon name="plus" class="size-3.5" />
                  Tambah List
                </button>
              @endif
            </div>

            @php
              $folders = $lists->filter(fn($l) => $l->folder_id !== null)->groupBy(fn($l) => $l->folder->name);
              $listsWithoutFolder = $lists->filter(fn($l) => $l->folder_id === null);
            @endphp

            @foreach ($folders as $folderName => $folderLists)
              <div class="mb-4 ml-2">
                <div class="mb-2 flex items-center gap-1.5">
                  <flux:icon name="folder" class="size-3.5 text-zinc-400" />
                  <span class="text-xs font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    {{ $folderName }}
                  </span>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                  @foreach ($folderLists as $list)
                    @include('livewire.project.partials.taskboard-list-card', ['list' => $list])
                  @endforeach
                </div>
              </div>
            @endforeach

            @if ($listsWithoutFolder->isNotEmpty())
              <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($listsWithoutFolder as $list)
                  @include('livewire.project.partials.taskboard-list-card', ['list' => $list])
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @else
      <div
        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 py-20 dark:border-zinc-700 dark:bg-zinc-800/20">
        <div class="mb-4 rounded-full bg-zinc-100 p-4 dark:bg-zinc-500/20">
          <flux:icon name="clipboard-document-list" class="size-9 text-zinc-400 dark:text-zinc-500" />
        </div>
        <flux:heading size="lg">Belum ada list</flux:heading>
        <flux:subheading class="mt-1">Mulai dengan membuat Space dan List pertama Anda.</flux:subheading>
        <div class="mt-6 flex gap-2">
          <flux:button size="sm" variant="ghost"
            @click="$flux.modal('create-list-modal').show(); $wire.openCreateList()">
            Tambah List
          </flux:button>
          <flux:button size="sm" variant="primary"
            @click="$flux.modal('create-space-modal').show(); $wire.set('showCreateSpace', true)">
            Tambah Space
          </flux:button>
        </div>
      </div>
    @endif
  @endif

  @if ($activeTab === 'calendar')
    <div class="mb-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="calPrevMonth" />
        <flux:button size="sm" variant="ghost" wire:click="calToday">Hari Ini</flux:button>
        <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="calNextMonth" />
      </div>
      <h2 class="text-base font-semibold text-zinc-900 dark:text-white sm:text-lg">{{ $calMonthLabel }}</h2>
    </div>

    {{-- Space filter pills --}}
    @if ($this->spaces->isNotEmpty())
      <div class="mb-5 flex flex-wrap items-center gap-2">
        <span class="shrink-0 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
          Filter Space:
        </span>

        <button wire:click="calSelectAll"
          class="flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition-all
            {{ empty($calSelectedSpaceIds)
                ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500/60 dark:bg-indigo-500/15 dark:text-indigo-300'
                : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }}">
          <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
          </svg>
          Semua
        </button>

        {{-- Per-space pill --}}
        @foreach ($this->spaces as $sp)
          @php $isActive = in_array($sp->id, $calSelectedSpaceIds); @endphp
          <button wire:click="toggleCalSpace({{ $sp->id }})"
            class="flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition-all"
            style="
              border-color: {{ $isActive ? $sp->color : '' }};
              background-color: {{ $isActive ? $sp->color . '18' : '' }};
              color: {{ $isActive ? $sp->color : '' }};
              {{ !$isActive ? 'border-color: rgb(228 228 231); background-color: white; color: rgb(113 113 122);' : '' }}
            ">
            <span class="size-2 shrink-0 rounded-full"
              style="background-color: {{ $sp->color }}; opacity: {{ $isActive ? '1' : '0.5' }}"></span>
            {{ $sp->name }}
            @if ($isActive)
              <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            @endif
          </button>
        @endforeach

        @if (!empty($calSelectedSpaceIds))
          <span class="text-xs text-zinc-400 dark:text-zinc-500">
            {{ count($calSelectedSpaceIds) }} dari {{ $this->spaces->count() }} space dipilih
          </span>
        @endif
      </div>
    @endif

    {{-- Desktop calendar grid --}}
    <div class="hidden overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 sm:block">
      <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $idx => $dayName)
          <div
            class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400
            {{ $idx >= 5 ? 'bg-zinc-100/50 dark:bg-zinc-800/30' : '' }}">
            {{ $dayName }}
          </div>
        @endforeach
      </div>

      @foreach ($weeks as $week)
        <div class="grid grid-cols-7 border-b border-zinc-100 last:border-b-0 dark:border-zinc-800">
          @foreach ($week as $day)
            <div
              class="group/cell min-h-[130px] border-r border-zinc-100 p-1.5 transition-colors last:border-r-0 dark:border-zinc-800
              {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/50 dark:bg-zinc-900/30' : 'bg-white dark:bg-zinc-900' }}
              {{ $day['date']->isWeekend() ? 'bg-zinc-50 dark:bg-zinc-800/20' : '' }}
              {{ $day['isToday'] ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : '' }}"
              wire:key="gcal-{{ $day['date']->format('Y-m-d') }}">

              <div class="mb-1 flex items-center justify-between">
                <span
                  class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium
                  {{ $day['isToday'] ? 'bg-indigo-600 text-white' : '' }}
                  {{ !$day['isCurrentMonth'] ? 'text-zinc-300 dark:text-zinc-600' : 'text-zinc-700 dark:text-zinc-300' }}">
                  {{ $day['date']->day }}
                </span>

                @if ($day['tasks']->isNotEmpty())
                  <span class="rounded-full px-1.5 py-0.5 text-[9px] font-bold text-white"
                    style="background-color: {{ $day['tasks']->first()->taskList->space->color ?? '#6366f1' }}">
                    {{ $day['tasks']->count() }}
                  </span>
                @endif
              </div>

              <div class="space-y-0.5">
                @foreach ($day['tasks'] as $task)
                  <button
                    @click="$flux.modal('gen-task-detail').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                    class="w-full rounded-md border-l-[3px] bg-white/80 px-1.5 py-1 text-left shadow-sm transition hover:shadow-md dark:bg-zinc-800/80"
                    style="border-left-color: {{ $task->taskList->space->color ?? '#6366f1' }}"
                    title="{{ $task->title }} — {{ $task->taskList->space->name ?? '' }} / {{ $task->taskList->name ?? '' }}">
                    <div class="flex min-w-0 items-center gap-1">
                      @php
                        $calDone = $task->status?->type === 'closed';
                        $calOver = !$calDone && $task->due_date?->isPast();
                      @endphp
                      <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                        style="background-color: {{ $task->priority_color }}"></span>
                      <span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-zinc-800 dark:text-zinc-100">
                        {{ $task->title }}
                      </span>
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
                    </div>
                    <span class="mt-0.5 block truncate rounded px-1 py-0.5 text-[9px] font-medium text-white"
                      style="background-color: {{ $task->taskList->space->color ?? '#6366f1' }}">
                      {{ $task->taskList->space->name ?? '-' }} / {{ $task->taskList->name ?? '-' }}
                    </span>
                  </button>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>

    {{-- Mobile: list-style calendar --}}
    <div class="space-y-2 sm:hidden">
      @foreach ($weeks as $week)
        @foreach ($week as $day)
          @if ($day['isCurrentMonth'])
            @php $hasTasks = $day['tasks']->isNotEmpty(); @endphp
            <div
              class="rounded-xl border px-3 py-2.5 transition-colors
              {{ $day['isToday'] ? 'border-indigo-300 bg-indigo-50/50 dark:border-indigo-700 dark:bg-indigo-900/10' : 'border-zinc-100 dark:border-zinc-800' }}
              {{ !$hasTasks ? 'opacity-50' : '' }}"
              wire:key="gcal-m-{{ $day['date']->format('Y-m-d') }}">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold
                    {{ $day['isToday'] ? 'bg-indigo-600 text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                    {{ $day['date']->day }}
                  </span>
                  <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500">
                    {{ $day['date']->isoFormat('ddd') }}
                  </span>
                </div>
                @if ($hasTasks)
                  <span
                    class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    {{ $day['tasks']->count() }}
                  </span>
                @endif
              </div>

              @if ($hasTasks)
                <div class="mt-2 space-y-1 pl-9">
                  @foreach ($day['tasks'] as $task)
                    <button
                      @click="$flux.modal('gen-task-detail').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                      class="flex w-full items-center gap-2 rounded-lg border-l-2 px-2 py-1.5 text-left text-xs font-medium transition hover:bg-zinc-50 dark:hover:bg-zinc-800"
                      style="border-left-color: {{ $task->taskList->space->color ?? '#6366f1' }}">
                      @php
                        $calDone = $task->status?->type === 'closed';
                        $calOver = !$calDone && $task->due_date?->isPast();
                      @endphp
                      <span class="h-2 w-2 shrink-0 rounded-full"
                        style="background-color: {{ $task->priority_color }}"></span>
                      <div class="min-w-0 flex-1">
                        <span class="block truncate text-zinc-900 dark:text-zinc-100">{{ $task->title }}</span>
                        <span class="block truncate text-[10px] text-zinc-400 dark:text-zinc-500">
                          {{ $task->taskList->space->name ?? '-' }} / {{ $task->taskList->name ?? '-' }}
                        </span>
                      </div>
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
                    </button>
                  @endforeach
                </div>
              @endif
            </div>
          @endif
        @endforeach
      @endforeach
    </div>
  @endif

  {{-- ─── Modals ──────────────────────────────────────────── --}}

  {{-- Create Space --}}
  <flux:modal name="create-space-modal" wire:model="showCreateSpace" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Buat Space Baru</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Space adalah wadah untuk mengelompokkan task list.</p>
      </div>

      <form wire:submit="createSpace" class="space-y-4">
        <flux:field>
          <flux:label>Nama Space</flux:label>
          <flux:input wire:model="spaceName" placeholder="Contoh: Design, Engineering, Marketing..." autofocus />
          <flux:error name="spaceName" />
        </flux:field>

        <div>
          <flux:label class="mb-2">Warna</flux:label>
          <div class="flex flex-wrap gap-2">
            @foreach (['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#f59e0b', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6'] as $color)
              <button type="button" wire:click="$set('spaceColor', '{{ $color }}')"
                class="h-8 w-8 rounded-full transition-all hover:scale-110 {{ $spaceColor === $color ? 'ring-2 ring-offset-2 ring-zinc-900 scale-110 dark:ring-white dark:ring-offset-zinc-900' : '' }}"
                style="background-color: {{ $color }}"></button>
            @endforeach
          </div>
        </div>

        <div>
          <flux:label class="mb-2">Ikon</flux:label>
          <div class="flex flex-wrap gap-1.5">
            @foreach (['folder', 'squares-2x2', 'briefcase', 'rocket-launch', 'star', 'bolt', 'fire', 'globe-alt', 'heart', 'cube'] as $icon)
              <button type="button" wire:click="$set('spaceIcon', '{{ $icon }}')"
                class="flex h-9 w-9 items-center justify-center rounded-lg border transition-colors
                {{ $spaceIcon === $icon ? 'border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'border-zinc-200 text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-400' }}">
                <flux:icon name="{{ $icon }}" class="size-4" />
              </button>
            @endforeach
          </div>
        </div>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('create-space-modal').close()">
            Batal
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Buat Space</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  {{-- Create List --}}
  <flux:modal name="create-list-modal" wire:model="showCreateList" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Buat List Baru</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">List berisi task-task yang dikelola dalam sebuah
          space.</p>
      </div>

      <form wire:submit="createList" class="space-y-4">
        <flux:field>
          <flux:label>Nama List</flux:label>
          <flux:input wire:model="listName" placeholder="Contoh: Sprint 1, Backlog, Q1 Tasks..." autofocus />
          <flux:error name="listName" />
        </flux:field>

        <flux:field>
          <flux:label>Space</flux:label>
          <flux:select wire:model="listSpaceId" placeholder="Pilih space...">
            @foreach ($this->spaces as $sp)
              <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
            @endforeach
          </flux:select>
          <flux:error name="listSpaceId" />
        </flux:field>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('create-list-modal').close()">
            Batal
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Buat List</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  {{-- Edit List --}}
  <flux:modal wire:model="showEditList" class="w-full max-w-md">
    <div class="space-y-6">
      <flux:heading size="lg">Edit List</flux:heading>
      <form wire:submit="updateList" class="space-y-4">
        <flux:field>
          <flux:label>Nama List</flux:label>
          <flux:input wire:model="editListName" placeholder="Masukkan nama list" autofocus />
          <flux:error name="editListName" />
        </flux:field>
        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showEditList', false)">Batal
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  {{-- Manage Members --}}
  <flux:modal wire:model="showManageMembers" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Kelola Anggota List</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Anggota yang terdaftar dapat di-assign ke task dalam
          list ini.</p>
      </div>

      <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700">
        @foreach ($this->allUsers as $user)
          <label wire:key="gtb-user-{{ $user->id }}"
            class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800">
            <flux:checkbox wire:model="listMemberIds" :value="$user->id" />
            <flux:avatar circle :name="$user->name" :initials="$user->initials()" :src="$user->avatar"
              size="sm" />
            <div class="min-w-0 flex-1">
              <span class="block font-medium text-zinc-800 dark:text-zinc-200">{{ $user->name }}</span>
              <span class="block truncate text-xs text-zinc-400">{{ $user->email }}</span>
            </div>
          </label>
        @endforeach
      </div>

      <div class="flex items-center justify-between">
        <span class="text-xs text-zinc-400">{{ count($listMemberIds) }} anggota dipilih</span>
        <div class="flex gap-2">
          <flux:button variant="ghost" @click="$wire.set('showManageMembers', false)">Batal</flux:button>
          <flux:button variant="primary" wire:click="saveMembers">Simpan Anggota</flux:button>
        </div>
      </div>
    </div>
  </flux:modal>

  {{-- Task Detail Modal (from calendar) --}}
  <flux:modal name="gen-task-detail" wire:model="showTaskDetail" :closable="false"
    @close="$wire.closeTaskDetail()"
    @task-deleted.window="if ($event.detail.taskId === $wire.selectedTaskId) $flux.modal('gen-task-detail').close()"
    class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
    <div
      class="relative min-h-[60vh] max-h-[85vh] overflow-y-auto px-1 -mx-1 max-sm:max-h-none max-sm:min-h-0 max-sm:h-[calc(100dvh-4rem)]">
      <div wire:loading wire:target="openTaskDetail" class="absolute inset-0 z-50 bg-white dark:bg-zinc-900">
        @include('livewire.project.partials.task-detail-skeleton')
      </div>
      @if ($selectedTaskId)
        <livewire:project.task-detail :taskId="$selectedTaskId" :key="'gen-detail-' . $selectedTaskId" />
      @else
        <div wire:loading.remove wire:target="openTaskDetail" class="flex min-h-[60vh] items-center justify-center">
          <div
            class="size-6 animate-spin rounded-full border-2 border-zinc-200 border-t-indigo-500 dark:border-zinc-700 dark:border-t-indigo-400">
          </div>
        </div>
      @endif
    </div>
  </flux:modal>
</div>
