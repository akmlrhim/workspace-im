<div x-data="{ deletingSpaceId: null, deletingSpaceName: '', deletedSpaceIds: [], deletingListId: null, deletingListName: '', deletedListIds: [] }">
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
        <flux:button size="sm" variant="ghost" icon="plus" class="cursor-pointer"
          @click="$wire.listSpaceId = null; $wire.listName = ''; $wire.showCreateList = true; $flux.modal('create-list-modal').show()">
          Tambah List
        </flux:button>

        <flux:button size="sm" variant="primary" icon="plus" class="cursor-pointer"
          @click="$wire.showCreateSpace = true; $flux.modal('create-space-modal').show()">
          Tambah Space
        </flux:button>
      @endif
    </div>
  </div>

  <div class="mb-6 flex items-center gap-1 border-b border-zinc-200 dark:border-zinc-700">
    <button wire:click="$set('activeTab', 'lists')"
      class="cursor-pointer flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
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
      class="cursor-pointer flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
        {{ $activeTab === 'calendar'
            ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="calendar-days" class="size-4" />
      Kalender Deadline
    </button>
  </div>

  @if ($activeTab === 'lists')
    @if ($spaces->isNotEmpty())
      <div class="space-y-8">
        @foreach ($spaces as $space)
          <div x-show="!deletedSpaceIds.includes({{ $space->id }})"
            x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" wire:key="space-{{ $space->id }}">
            <div class="mb-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                  <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md"
                    style="background-color: {{ $space->color }}20">
                    <flux:icon name="{{ $space->icon }}" class="size-4" style="color: {{ $space->color }}" />
                  </div>
                  <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ $space->name }}
                  </h2>
                </div>
                <span
                  class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                  {{ $space->lists->count() }}
                </span>
              </div>

              @if (auth()->user()->canManageLists())
                <div class="flex items-center gap-0.5">
                  <button title="Edit Space"
                    @click="$wire.editingSpaceId = {{ $space->id }}; $wire.editSpaceName = @js($space->name); $wire.editSpaceColor = @js($space->color); $wire.editSpaceIcon = @js($space->icon); $wire.showEditSpace = true"
                    class="cursor-pointer flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 transition hover:bg-zinc-100 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400"
                    title="Edit Space">
                    <flux:icon name="pencil-square" class="size-3.5" />
                  </button>
                  <button title="Hapus Space"
                    @click="deletingSpaceId = {{ $space->id }}; deletingSpaceName = @js($space->name); $flux.modal('delete-space-modal').show()"
                    class="cursor-pointer flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                    title="Hapus Space">
                    <flux:icon name="trash" class="size-3.5" />
                  </button>
                </div>
              @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              @foreach ($space->lists as $list)
                <div x-show="!deletedListIds.includes({{ $list->id }})"
                  x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100"
                  x-transition:leave-end="opacity-0">
                  @include('livewire.project.partials.taskboard-list-card', ['list' => $list])
                </div>
              @endforeach
            </div>
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
            @click="$wire.listSpaceId = null; $wire.listName = ''; $wire.showCreateList = true; $flux.modal('create-list-modal').show()">
            Tambah List
          </flux:button>
          <flux:button size="sm" variant="primary"
            @click="$wire.showCreateSpace = true; $flux.modal('create-space-modal').show()">
            Tambah Space
          </flux:button>
        </div>
      </div>
    @endif
  @endif

  @if ($activeTab === 'calendar')
    @php $selectedSpace = $calSelectedSpaceId ? $this->spaces->firstWhere('id', $calSelectedSpaceId) : null; @endphp

    <div class="mb-4 flex items-center justify-between gap-4">
      <div class="flex shrink-0 items-center gap-1">
        <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="calPrevMonth" />
        <flux:button size="sm" variant="ghost" wire:click="calToday">Hari Ini</flux:button>
        <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="calNextMonth" />
      </div>

      <h2 class="truncate text-base font-semibold text-zinc-900 dark:text-white sm:text-lg">{{ $calMonthLabel }}</h2>

      @if ($this->spaces->isNotEmpty())
        <div class="flex shrink-0 items-center gap-1.5">
          <flux:select wire:model.live="calSelectedSpaceId" size="sm" class="w-36 sm:w-44">
            @foreach ($this->spaces as $sp)
              <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
            @endforeach
          </flux:select>
        </div>
      @endif
    </div>

    <div
      class="hidden overflow-hidden rounded-xl border border-zinc-200 transition-opacity duration-150 dark:border-zinc-700 sm:block"
      wire:loading.class="opacity-40" wire:target="calPrevMonth,calNextMonth,calToday">
      <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $idx => $dayName)
          <div
            class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-white
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
              {{ $day['date']->isWeekend() && !$day['isToday'] ? 'bg-zinc-50 dark:bg-zinc-800/20' : '' }}
              {{ $day['isToday'] ? 'border-t-2 border-t-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : '' }}"
              wire:key="gcal-{{ $day['date']->format('Y-m-d') }}">

              <div class="mb-1 flex items-center justify-between">
                <span
                  class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold
                  {{ $day['isToday'] ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300 dark:shadow-indigo-900' : '' }}
                  {{ !$day['isCurrentMonth'] && !$day['isToday'] ? 'text-zinc-300 dark:text-zinc-600' : (!$day['isToday'] ? 'text-zinc-700 dark:text-zinc-300' : '') }}">
                  {{ $day['date']->day }}
                </span>

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
                      @endphp
                      <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                        style="background-color: {{ $task->priority_color }}"></span>
                      <span x-data="{ title: @js($task->title) }"
                        @task-title-updated.window="if ($event.detail.taskId === {{ $task->id }}) title = $event.detail.title"
                        x-text="title"
                        class="min-w-0 flex-1 truncate text-[11px] font-semibold text-zinc-800 dark:text-zinc-100"></span>
                      @if ($calDone)
                        <flux:icon name="check-circle" class="size-4 font-bold text-green-600 dark:text-green-400" />
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
    <div class="space-y-2 transition-opacity duration-150 sm:hidden" wire:loading.class="opacity-40"
      wire:target="calPrevMonth,calNextMonth,calToday">
      @foreach ($weeks as $week)
        @foreach ($week as $day)
          @if ($day['isCurrentMonth'])
            @php $hasTasks = $day['tasks']->isNotEmpty(); @endphp
            <div
              class="rounded-xl border px-3 py-2.5 transition-colors
              {{ $day['isToday'] ? 'border-indigo-400 bg-indigo-50 ring-1 ring-indigo-300 dark:border-indigo-600 dark:bg-indigo-950/40 dark:ring-indigo-700' : 'border-zinc-100 dark:border-zinc-800' }}
              {{ !$hasTasks && !$day['isToday'] ? 'opacity-50' : '' }}"
              wire:key="gcal-m-{{ $day['date']->format('Y-m-d') }}">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold
                    {{ $day['isToday'] ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300 dark:shadow-indigo-900' : 'text-zinc-700 dark:text-zinc-300' }}">
                    {{ $day['date']->day }}
                  </span>
                  <span
                    class="text-xs font-medium {{ $day['isToday'] ? 'font-bold text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}">
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
                      @endphp
                      <span class="h-2 w-2 shrink-0 rounded-full"
                        style="background-color: {{ $task->priority_color }}"></span>
                      <div class="min-w-0 flex-1">
                        <span x-data="{ title: @js($task->title) }"
                          @task-title-updated.window="if ($event.detail.taskId === {{ $task->id }}) title = $event.detail.title"
                          x-text="title" class="block truncate text-zinc-900 dark:text-zinc-100"></span>
                        <span class="block truncate text-[10px] text-zinc-400 dark:text-zinc-500">
                          {{ $task->taskList->space->name ?? '-' }} / {{ $task->taskList->name ?? '-' }}
                        </span>
                      </div>

                      @if ($calDone)
                        <flux:icon name="check-circle" class="size-4 font-bold text-green-600 dark:text-green-400" />
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
          <flux:input wire:model="createSpaceForm.name" placeholder="Contoh: Divisi, Proyek, HQ, Arsip....."
            autofocus />
          <flux:error name="createSpaceForm.name" />
        </flux:field>

        <div>
          <flux:label class="mb-2">Warna</flux:label>
          <div class="flex flex-wrap gap-2">
            @foreach (['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#f59e0b', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6'] as $color)
              <button type="button" @click="$wire.createSpaceForm.color = '{{ $color }}'"
                class="h-8 w-8 rounded-full transition-all hover:scale-110"
                :class="$wire.createSpaceForm.color === '{{ $color }}' ?
                    'ring-2 ring-offset-2 ring-zinc-900 scale-110 dark:ring-white dark:ring-offset-zinc-900' : ''"
                style="background-color: {{ $color }}"></button>
            @endforeach
          </div>
        </div>

        <div>
          <flux:label class="mb-2">Ikon</flux:label>
          <div class="flex flex-wrap gap-1.5">
            @foreach (['folder', 'squares-2x2', 'briefcase', 'rocket-launch', 'star', 'bolt', 'fire', 'globe-alt', 'heart', 'cube'] as $icon)
              <button type="button" @click="$wire.createSpaceForm.icon = '{{ $icon }}'"
                class="flex h-9 w-9 items-center justify-center rounded-lg border transition-colors"
                :class="$wire.createSpaceForm.icon === '{{ $icon }}' ?
                    'border-zinc-900 bg-zinc-100 text-zinc-900 dark:border-zinc-400 dark:bg-zinc-700 dark:text-white' :
                    'border-zinc-200 text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-400'">
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
          <flux:input wire:model="createListForm.name" placeholder="Finance, HR, Creative......" autofocus />
          <flux:error name="createListForm.name" />
        </flux:field>

        <flux:field>
          <flux:label>Space</flux:label>
          <flux:select wire:model="createListForm.spaceId" placeholder="Pilih space...">
            @foreach ($this->spaces as $sp)
              <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
            @endforeach
          </flux:select>
          <flux:error name="createListForm.spaceId" />
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

  {{-- Edit Space --}}
  <flux:modal wire:model="showEditSpace" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Edit Space</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ubah nama, warna, atau ikon space.</p>
      </div>

      <form wire:submit="updateSpace" class="space-y-4">
        <flux:field>
          <flux:label>Nama Space</flux:label>
          <flux:input wire:model="editSpaceName" placeholder="Masukkan nama space" autofocus />
          <flux:error name="editSpaceName" />
        </flux:field>

        <div>
          <flux:label class="mb-2">Warna</flux:label>
          <div class="flex flex-wrap gap-2">
            @foreach (['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#f59e0b', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6'] as $color)
              <button type="button" @click="$wire.editSpaceColor = '{{ $color }}'"
                class="h-8 w-8 rounded-full transition-all hover:scale-110"
                :class="$wire.editSpaceColor === '{{ $color }}' ?
                    'ring-2 ring-offset-2 ring-zinc-900 scale-110 dark:ring-white dark:ring-offset-zinc-900' : ''"
                style="background-color: {{ $color }}"></button>
            @endforeach
          </div>
        </div>

        <div>
          <flux:label class="mb-2">Ikon</flux:label>
          <div class="flex flex-wrap gap-1.5">
            @foreach (['folder', 'squares-2x2', 'briefcase', 'rocket-launch', 'star', 'bolt', 'fire', 'globe-alt', 'heart', 'cube'] as $icon)
              <button type="button" @click="$wire.editSpaceIcon = '{{ $icon }}'"
                class="flex h-9 w-9 items-center justify-center rounded-lg border transition-colors"
                :class="$wire.editSpaceIcon === '{{ $icon }}' ?
                    'border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' :
                    'border-zinc-200 text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-400'">
                <flux:icon name="{{ $icon }}" class="size-4" />
              </button>
            @endforeach
          </div>
        </div>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showEditSpace', false)">
            Batal
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  {{-- Edit List --}}
  <flux:modal name="edit-list-modal" wire:model="showEditList" class="w-full max-w-md">
    <div class="space-y-6">
      <flux:heading size="lg">Edit List</flux:heading>
      <form wire:submit="updateList" class="space-y-4">
        <flux:field>
          <flux:label>Nama List</flux:label>
          <flux:input wire:model="editListName" placeholder="Masukkan nama list" autofocus />
          <flux:error name="editListName" />
        </flux:field>
        <flux:field>
          <flux:label>Space</flux:label>
          <flux:select wire:model="editListSpaceId" placeholder="Pilih space tujuan...">
            @foreach ($this->spaces as $sp)
              <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
            @endforeach
          </flux:select>
          <flux:error name="editListSpaceId" />
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
  <flux:modal name="manage-members-modal" wire:model="showManageMembers" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Kelola Anggota List</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Anggota yang terdaftar dapat di-assign ke task dalam
          list ini.</p>
      </div>

      <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700">
        <div wire:loading wire:target="openManageMembers" class="flex min-h-[120px] items-center justify-center">
          <div
            class="size-6 animate-spin rounded-full border-2 border-zinc-200 border-t-indigo-500 dark:border-zinc-700 dark:border-t-indigo-400">
          </div>
        </div>
        <div wire:loading.remove wire:target="openManageMembers">
          @foreach ($this->allUsers as $user)
            <label wire:key="gtb-user-{{ $user->id }}"
              class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800">
              <input type="checkbox" wire:model.live="listMemberIds" value="{{ $user->id }}"
                class="size-4 cursor-pointer rounded border-zinc-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-700 dark:checked:bg-indigo-500" />
              <flux:avatar circle :name="$user->name" :initials="$user->initials()" :src="$user->avatar"
                size="sm" />
              <div class="min-w-0 flex-1">
                <span class="block font-medium text-zinc-800 dark:text-zinc-200">{{ $user->name }}</span>
                <span class="block truncate text-xs text-zinc-400">{{ $user->email }}</span>
              </div>
            </label>
          @endforeach
        </div>
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

  {{-- Delete List --}}
  <flux:modal name="delete-list-modal" class="w-full max-w-sm">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Hapus List</flux:heading>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
          List <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="deletingListName"></span>
          beserta seluruh task di dalamnya akan dihapus permanen.
        </p>
      </div>
      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('delete-list-modal').close()">
          Batal
        </flux:button>
        <flux:button variant="danger" class="w-full sm:w-auto"
          @click="deletedListIds.push(deletingListId); $wire.deleteList(deletingListId); $flux.modal('delete-list-modal').close()">
          Hapus
        </flux:button>
      </div>
    </div>
  </flux:modal>

  {{-- Delete Space --}}
  <flux:modal name="delete-space-modal" class="w-full max-w-sm">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Hapus Space</flux:heading>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
          Space <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="deletingSpaceName"></span>
          beserta seluruh list dan task di dalamnya akan dihapus permanen.
        </p>
      </div>
      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('delete-space-modal').close()">
          Batal
        </flux:button>
        <flux:button variant="danger" class="w-full sm:w-auto"
          @click="deletedSpaceIds.push(deletingSpaceId); $wire.deleteSpace(deletingSpaceId); $flux.modal('delete-space-modal').close()">
          Hapus
        </flux:button>
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
