{{-- Read-only banner + sticky header (title, timer, delete, close) + tab bar --}}
@if ($ro)
  <div
    class="mb-4 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-400">
    <flux:icon name="lock-closed" class="size-3.5 shrink-0" />
    <span>Mode <strong>lihat saja</strong> — Anda tidak ditugaskan untuk tugas ini.</span>
  </div>
@endif

<div
  class="sticky top-0 z-10 -mx-1 rounded-t-xl border-b border-zinc-200 bg-white/95 px-1 pt-2 backdrop-blur max-sm:rounded-t-none dark:border-zinc-700/60 dark:bg-zinc-800/95">
  <div class="flex items-start justify-between gap-2">
    <div class="min-w-0 flex-1">
      <div x-data="{
          editing: false,
          draft: @js($taskTitle),
          start() {
              if (!{{ $canManage ? 'true' : 'false' }}) return;
              this.draft = this.$wire.taskTitle;
              this.editing = true;
              this.$nextTick(() => this.$refs.titleInput?.focus());
          },
          async save() {
              if (!this.editing) return;
              const next = (this.draft ?? '').trim();
              if (next === '' || next === this.$wire.taskTitle) {
                  this.cancel();
                  return;
              }
              this.draft = next;
              this.$wire.taskTitle = next;
              this.editing = false;
              window.dispatchEvent(new CustomEvent('task-title-updated', { detail: { taskId: this.$wire.taskId, title: next } }));
              this.$wire.saveTitle();
          },
          cancel() {
              this.draft = this.$wire.taskTitle;
              this.editing = false;
          },
      }" class="group relative">
        <button type="button" x-show="!editing" @click="start()"
          class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-lg font-semibold transition-colors {{ $canManage ? 'hover:bg-zinc-100 dark:hover:bg-zinc-800' : 'cursor-default' }}"
          title="{{ $canManage ? 'Klik untuk ubah judul' : '' }}">
          <span class="flex-1 truncate text-zinc-900 dark:text-zinc-100"
            x-text="draft !== '' ? draft : 'Tanpa Judul'"></span>
          @if ($canManage)
            <flux:icon name="pencil-square"
              class="size-4 shrink-0 text-zinc-300 opacity-0 transition-opacity group-hover:opacity-100 dark:text-zinc-500" />
          @endif
        </button>

        @if ($canManage)
          <div x-show="editing" x-cloak>
            <input type="text" x-ref="titleInput" x-model="draft" maxlength="500" @keydown.enter.prevent="save()"
              @keydown.escape.prevent="cancel()" @blur="save()" placeholder="Judul tugas..."
              class="w-full rounded-md border border-indigo-300 bg-white px-2 py-1.5 text-lg font-semibold text-zinc-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-indigo-500/60 dark:bg-zinc-900 dark:text-zinc-100" />
            <p class="mt-1 text-[11px] text-zinc-400 dark:text-zinc-500">Enter untuk simpan · Esc untuk batal</p>
          </div>
        @endif
      </div>
    </div>

    <div class="flex shrink-0 items-center gap-1 pt-1.5">
      @if ($canManage)
        @if ($activeTimerId)
          <flux:button icon="stop-circle" size="sm" variant="danger" wire:click="stopTimer" class="relative">
            <span class="hidden sm:inline">Stop</span>
            <span class="absolute -right-1 -top-1 flex h-2.5 w-2.5">
              <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
            </span>
          </flux:button>
        @else
          <flux:button icon="play-circle" size="sm" variant="ghost" wire:click="startTimer" title="Mulai Timer" />
        @endif

        <flux:button icon="trash" size="sm" variant="ghost"
          class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
          wire:click="$dispatch('open-delete-task-modal', { taskId: {{ $task->id }} })" title="Hapus Tugas" />
      @endif

      <flux:modal.close>
        <flux:button icon="x-mark" size="sm" variant="ghost"
          class="text-zinc-400! hover:text-zinc-800! dark:text-zinc-500! dark:hover:text-white!" title="Tutup" />
      </flux:modal.close>
    </div>
  </div>

  {{-- Tabs --}}
  <div class="mt-2 flex items-center gap-1 overflow-x-auto">
    @php
      $tabs = [
          ['key' => 'overview', 'label' => 'Overview', 'count' => null, 'icon' => 'document-text'],
          [
              'key' => 'checklist',
              'label' => 'Checklist',
              'count' => $checklistTotal > 0 ? $checklistDone . '/' . $checklistTotal : null,
              'icon' => 'check-circle',
          ],
          [
              'key' => 'comments',
              'label' => 'Komentar',
              'count' => $commentCount ?: null,
              'icon' => 'chat-bubble-left',
          ],
          ['key' => 'activity', 'label' => 'Aktivitas', 'count' => null, 'icon' => 'clock'],
      ];
    @endphp
    @foreach ($tabs as $tab)
      <button type="button" @click="activeTab = '{{ $tab['key'] }}'"
        :class="activeTab === '{{ $tab['key'] }}' ?
            'border-indigo-500 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' :
            'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
        class="flex shrink-0 items-center gap-1.5 border-b-2 px-3 py-2 text-sm font-medium transition-colors">
        <flux:icon name="{{ $tab['icon'] }}" class="size-4" />
        {{ $tab['label'] }}
        @if ($tab['count'] !== null)
          <span
            :class="activeTab === '{{ $tab['key'] }}' ?
                'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' :
                'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
            class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold">
            {{ $tab['count'] }}
          </span>
        @endif
      </button>
    @endforeach
  </div>
</div>
