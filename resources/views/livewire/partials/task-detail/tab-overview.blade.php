<div x-show="activeTab === 'overview'" x-cloak class="space-y-6">
  <div
    class="grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 sm:grid-cols-3 dark:border-zinc-700/50 dark:bg-zinc-800/20">
    <div>
      <label
        class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</label>
      <flux:select wire:model.live="taskStatusId" wire:change="updateStatus($event.target.value)" :disabled="$ro"
        size="sm">
        @foreach ($statuses as $status)
          <flux:select.option value="{{ $status->id }}">{{ $status->name }}</flux:select.option>
        @endforeach
      </flux:select>
    </div>

    <div>
      <label
        class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Prioritas</label>
      <flux:select wire:model.live="taskPriority" wire:change="updatePriority($event.target.value)" :disabled="$ro"
        size="sm">
        <flux:select.option value="urgent">🔴 Urgent</flux:select.option>
        <flux:select.option value="high">🟠 High</flux:select.option>
        <flux:select.option value="normal">🔵 Normal</flux:select.option>
        <flux:select.option value="low">⚪ Low</flux:select.option>
      </flux:select>
    </div>

    <div>
      <label
        class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tenggat</label>
      <flux:input type="date" onclick="this.showPicker()" wire:model="taskDueDate" wire:change="updateDueDate"
        :readonly="$ro" size="sm" />
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div x-data="{ openMenu: false }">
      <div class="mb-3 flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-700/50">
        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Assignees</span>
        @if ($canManage)
          <flux:button icon="plus" size="xs" variant="ghost" @click="openMenu = !openMenu"
            class="h-7 px-2 text-xs text-zinc-500 hover:text-indigo-600 dark:hover:text-indigo-400">
            Tambah
          </flux:button>
        @endif
      </div>

      <div class="flex flex-wrap gap-2">
        @forelse ($task->assignees as $assignee)
          <span
            class="inline-flex items-center gap-1.5 rounded-full bg-white border border-zinc-200 px-2 py-1 text-xs font-medium text-zinc-700 shadow-sm dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
            <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar"
              size="xs" class="size-5" />
            {{ $assignee->name }}
          </span>
        @empty
          <span class="text-xs italic text-zinc-400">Belum ada assignee</span>
        @endforelse
      </div>

      @if ($canManage)
        <div x-show="openMenu" x-collapse x-cloak class="mt-3">
          @if ($workspaceUsers->isEmpty())
            <div
              class="rounded-lg border border-dashed border-zinc-200 p-3 text-center text-xs text-zinc-500 dark:border-zinc-700">
              Belum ada anggota. Tambahkan di pengaturan Space.
            </div>
          @else
            <div
              class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 dark:border-zinc-700 dark:bg-zinc-900/50 custom-scrollbar">
              @foreach ($workspaceUsers as $member)
                <label
                  class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-white dark:hover:bg-zinc-800">
                  <flux:checkbox wire:model="taskAssigneeIds" :value="$member->id" wire:change="updateAssignees" />
                  <flux:avatar circle :name="$member->name" :initials="$member->initials()" :src="$member->avatar"
                    size="xs" />
                  <span class="text-zinc-700 dark:text-zinc-300">{{ $member->name }}</span>
                </label>
              @endforeach
            </div>
          @endif
        </div>
      @endif
    </div>

    @include('livewire.partials.task-detail.overview-labels')
  </div>

  <div>
    <h3 class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Deskripsi / Catatan</h3>
    <flux:textarea wire:model.blur="taskDescription" wire:change="saveDescription" :readonly="$ro" rows="4"
      placeholder="{{ $canManage ? 'Tambahkan deskripsi rinci...' : 'Tidak ada deskripsi.' }}" />
  </div>

  @include('livewire.partials.task-detail.overview-attachments')
</div>
