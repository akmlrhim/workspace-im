{{-- Checklist tab: checklists, their items and the per-item detail panel --}}
<div x-show="activeTab === 'checklist'" x-cloak>
  <div class="mb-3 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
      Checklist
      @if ($checklistTotal > 0)
        <span class="ml-1 text-xs text-zinc-400">({{ $checklistDone }}/{{ $checklistTotal }} selesai)</span>
      @endif
    </h3>
    @if ($canManage)
      <flux:button icon="plus" size="xs" variant="ghost" wire:click="$toggle('showChecklistForm')">
        Tambah Checklist
      </flux:button>
    @endif
  </div>

  @if ($canManage && $showChecklistForm)
    <form wire:submit="addChecklist" class="mb-4 flex gap-2">
      <flux:input wire:model="newChecklistName" placeholder="Nama checklist..." size="sm" class="flex-1" autofocus />
      <flux:button type="submit" size="sm" variant="primary">Tambah</flux:button>
      <flux:button type="button" size="sm" variant="ghost" wire:click="$set('showChecklistForm', false)">
        Batal</flux:button>
    </form>
  @endif

  @forelse ($checklists as $checklist)
    @php
      $allItems = $checklist->items;
      $doneCount = $allItems->where('is_completed', true)->count();
      $totalItems = $allItems->count();
      $pct = $totalItems > 0 ? round(($doneCount / $totalItems) * 100) : 0;
    @endphp

    <div class="mb-8" wire:key="cl-{{ $checklist->id }}">

      <div class="mb-2 flex items-center justify-between gap-3" x-data="{ editing: false, name: {{ Js::from($checklist->name) }} }">
        <div class="flex items-center gap-2 flex-1 min-w-0">
          <flux:icon name="check-circle" class="size-5 shrink-0 text-indigo-500" />

          <div class="group flex flex-1 items-center gap-2"
            @if ($canManage) @click="editing = true; $nextTick(() => $refs['cl_name_{{ $checklist->id }}'].focus())" @endif>

            <span x-show="!editing"
              class="truncate text-base font-bold text-zinc-800 dark:text-zinc-100 {{ $canManage ? 'cursor-pointer hover:text-indigo-600 transition-colors' : '' }}">
              {{ $checklist->name }}
            </span>

            @if ($canManage)
              <flux:icon x-show="!editing" name="pencil"
                class="size-3.5 text-zinc-300 opacity-0 transition-opacity group-hover:opacity-100 dark:text-zinc-500" />
            @endif

            <input x-show="editing" x-cloak x-ref="cl_name_{{ $checklist->id }}" x-model="name"
              x-on:keydown.enter="$wire.editChecklistName({{ $checklist->id }}, name); editing = false"
              x-on:keydown.escape="editing = false" x-on:blur="editing = false"
              class="flex-1 rounded-md border border-indigo-300 bg-white px-2 py-1 text-sm text-zinc-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $doneCount }}/{{ $totalItems }}</span>
          @if ($canManage)
            <button wire:click="deleteChecklist({{ $checklist->id }})"
              class="rounded p-1 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10 dark:hover:text-red-400"
              title="Hapus Checklist">
              <flux:icon name="trash" class="size-4" />
            </button>
          @endif
        </div>
      </div>

      <div class="mb-3 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
        <div
          class="h-full rounded-full transition-all duration-500 ease-out {{ $pct == 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
          style="width: {{ $pct }}%"></div>
      </div>

      <div class="space-y-1.5" x-data="{ editingItemId: null, editTitle: '' }">
        @foreach ($allItems as $item)
          @php $isActive = $activeChecklistItemId === $item->id; @endphp

          <div wire:key="cli-{{ $item->id }}"
            class="rounded-xl border transition-all duration-200 {{ $isActive ? 'border-indigo-200 bg-indigo-50/30 shadow-sm dark:border-indigo-900/50 dark:bg-indigo-900/10' : 'border-transparent' }}">

            <div
              class="group flex items-center gap-3 px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 rounded-xl transition-colors">

              <button @if ($canManage) wire:click="toggleChecklistItem({{ $item->id }})" @else disabled @endif
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition-all duration-200
                  {{ $item->is_completed ? 'border-green-500 bg-green-500 shadow-sm' : 'border-zinc-300 dark:border-zinc-600' }}
                  {{ $canManage ? 'hover:border-indigo-400 hover:scale-105' : 'cursor-not-allowed opacity-60' }}">
                @if ($item->is_completed)
                  <flux:icon name="check" class="size-3.5 text-white stroke-[3]" />
                @endif
              </button>

              <div class="flex-1 min-w-0 flex items-center gap-2"
                @if ($canManage) @click="editingItemId = {{ $item->id }}; editTitle = {{ Js::from($item->title) }}; $nextTick(() => $refs['edit_cli_{{ $item->id }}'].focus())" @endif>

                <span x-show="editingItemId !== {{ $item->id }}"
                  class="truncate text-sm transition-colors {{ $item->is_completed ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-700 dark:text-zinc-200' }} {{ $canManage ? 'cursor-pointer hover:text-indigo-600' : '' }}">
                  {{ $item->title }}
                </span>

                @if ($canManage && !$item->is_completed)
                  <flux:icon x-show="editingItemId !== {{ $item->id }}" name="pencil"
                    class="size-3 text-zinc-300 opacity-0 transition-opacity group-hover:opacity-100 dark:text-zinc-500" />
                @endif

                <input x-show="editingItemId === {{ $item->id }}" x-cloak x-ref="edit_cli_{{ $item->id }}"
                  x-model="editTitle"
                  x-on:keydown.enter="$wire.editChecklistItemTitle({{ $item->id }}, editTitle); editingItemId = null"
                  x-on:keydown.escape="editingItemId = null" x-on:blur="editingItemId = null"
                  class="flex-1 rounded border border-indigo-300 bg-white px-2 py-0.5 text-sm focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
              </div>

              <div class="flex shrink-0 items-center gap-2 {{ $canManage ? '' : 'opacity-60' }}">

                @if ($item->assignees->isNotEmpty())
                  <div class="flex -space-x-1.5">
                    @foreach ($item->assignees->take(3) as $a)
                      <flux:avatar circle :name="$a->name" :initials="$a->initials()" :src="$a->avatar"
                        size="xs" class="ring-2 ring-white dark:ring-zinc-900" />
                    @endforeach
                  </div>
                @endif

                @if ($item->attachments->isNotEmpty())
                  <span
                    class="flex items-center gap-1 text-xs text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded-md">
                    <flux:icon name="paper-clip" class="size-3" />
                    {{ $item->attachments->count() }}
                  </span>
                @endif

                @if ($item->due_date)
                  @php
                    $badgeClass = $item->is_completed
                        ? 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800'
                        : ($item->due_date->isPast()
                            ? 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800'
                            : 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700');
                  @endphp
                  <span class="rounded-md border px-1.5 py-0.5 text-[11px] font-medium tracking-wide {{ $badgeClass }}">
                    {{ $item->due_date->format('d M') }}
                    @if ($item->is_completed)
                      <svg class="inline size-3 shrink-0 text-green-500 dark:text-green-400" viewBox="0 0 16 16"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                          d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z"
                          clip-rule="evenodd" />
                      </svg>
                    @endif
                  </span>
                @endif

                @if ($canManage)
                  <div class="flex items-center gap-1 ml-1">
                    <button wire:click="openChecklistItemPanel({{ $item->id }})"
                      class="cursor-pointer flex items-center justify-center rounded-md p-1.5 transition-colors {{ $isActive ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-zinc-400 hover:bg-zinc-100 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400' }}"
                      title="Detail Tugas">
                      <flux:icon name="ellipsis-horizontal" class="size-4" />
                    </button>
                    <button wire:click="deleteChecklistItem({{ $item->id }})"
                      class="cursor-pointer rounded-md p-1.5 text-zinc-400 hover:bg-red-50 hover:text-red-600 transition-colors dark:hover:bg-red-500/10 dark:hover:text-red-400"
                      title="Hapus Sub Tugas">
                      <flux:icon name="trash" class="size-4" />
                    </button>
                  </div>
                @endif
              </div>
            </div>

            @if ($isActive)
              @include('livewire.project.partials.task-detail.checklist-item-panel')
            @endif
          </div>
        @endforeach
      </div>

      @if ($canManage)
        <div class="mt-3">
          @if ($addingItemToChecklistId === $checklist->id)
            <form wire:submit="addChecklistItem"
              class="flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50/50 p-2 dark:border-indigo-900/50 dark:bg-indigo-900/10">
              <flux:input wire:model="newChecklistItemTitle" placeholder="Judul sub tugas baru..." size="sm"
                class="flex-1 bg-white dark:bg-zinc-900" autofocus />
              <flux:button type="submit" size="sm" variant="primary">Simpan</flux:button>
              <flux:button type="button" size="sm" variant="ghost"
                wire:click="openAddChecklistItem({{ $checklist->id }})">Batal</flux:button>
            </form>
          @else
            <button wire:click="openAddChecklistItem({{ $checklist->id }})"
              class="group flex w-full items-center gap-2 rounded-xl border border-dashed border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:border-indigo-400 hover:bg-indigo-50/50 hover:text-indigo-600 dark:border-zinc-700 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-400">
              <div
                class="flex h-5 w-5 items-center justify-center rounded-md bg-zinc-100 transition-colors group-hover:bg-indigo-100 dark:bg-zinc-800 dark:group-hover:bg-indigo-900/50">
                <flux:icon name="plus" class="size-3.5" />
              </div>
              Tambah Sub Tugas
            </button>
          @endif
        </div>
      @endif
    </div>
  @empty
    @if (!$showChecklistForm)
      <div
        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 px-4 py-10 text-center dark:border-zinc-700/60">
        <flux:icon name="check-circle" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Belum ada checklist.</p>
        @if ($canManage)
          <p class="mt-1 text-xs text-zinc-400">Klik "Tambah Checklist" di atas untuk memulai.</p>
        @endif
      </div>
    @endif
  @endforelse
</div>
