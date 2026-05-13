@php $ro = !$canManage; @endphp
<div x-data="{ activeTab: @entangle('activeTab') }">
  @if ($task)
    @php
      $checklistTotal = $checklists->sum(fn($c) => $c->items->count());
      $checklistDone = $checklists->sum(fn($c) => $c->items->where('is_completed', true)->count());
      $commentCount = $comments->count();
    @endphp

    @if ($ro)
      <div
        class="mb-4 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-400">
        <flux:icon name="lock-closed" class="size-3.5 shrink-0" />
        <span>Mode <strong>lihat saja</strong> — Anda tidak ditugaskan untuk tugas ini.</span>
      </div>
    @endif

    {{-- Sticky Header --}}
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
                  class="w-full rounded-md border border-indigo-300 bg-white px-2 py-1.5 text-lg font-semibold text-zinc-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-indigo-500/60 dark:bg-zinc-900 dark:text-zinc-100" />
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
                  <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                </span>
              </flux:button>
            @else
              <flux:button icon="play-circle" size="sm" variant="ghost" wire:click="startTimer"
                title="Mulai Timer" />
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

    {{-- Tab Content --}}
    <div class="pt-6">

      <div x-show="activeTab === 'overview'" x-cloak class="space-y-6">
        {{-- Overview  --}}
        <div
          class="grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 sm:grid-cols-3 dark:border-zinc-700/50 dark:bg-zinc-800/20">
          <div>
            <label
              class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</label>
            <flux:select wire:model.live="taskStatusId" wire:change="updateStatus($event.target.value)"
              :disabled="$ro" size="sm">
              @foreach ($statuses as $status)
                <flux:select.option value="{{ $status->id }}">{{ $status->name }}</flux:select.option>
              @endforeach
            </flux:select>
          </div>

          <div>
            <label
              class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Prioritas</label>
            <flux:select wire:model.live="taskPriority" wire:change="updatePriority($event.target.value)"
              :disabled="$ro" size="sm">
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
          {{-- Assignees --}}
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
                        <flux:checkbox wire:model="taskAssigneeIds" :value="$member->id"
                          wire:change="updateAssignees" />
                        <flux:avatar circle :name="$member->name" :initials="$member->initials()"
                          :src="$member->avatar" size="xs" />
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $member->name }}</span>
                      </label>
                    @endforeach
                  </div>
                @endif
              </div>
            @endif
          </div>

          {{-- Labels --}}
          <div x-data="{ openMenu: false }">
            <div class="mb-3 flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-700/50">
              <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Labels</span>
              @if ($canManage)
                <flux:button icon="plus" size="xs" variant="ghost" @click="openMenu = !openMenu"
                  class="h-7 px-2 text-xs text-zinc-500 hover:text-indigo-600 dark:hover:text-indigo-400">
                  Atur Label
                </flux:button>
              @endif
            </div>

            <div class="flex flex-wrap gap-1.5">
              @forelse ($task->labels as $label)
                <span
                  class="group relative inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm transition-all"
                  style="background-color: {{ $label->color }}">
                  {{ $label->name }}
                  @if ($canManage)
                    <button wire:click="toggleLabel({{ $label->id }})"
                      class="ml-1 -mr-1 rounded-sm p-0.5 opacity-0 transition-opacity hover:bg-white/20 group-hover:opacity-100"
                      title="Hapus label">
                      <flux:icon name="x-mark" class="size-3" />
                    </button>
                  @endif
                </span>
              @empty
                <span class="text-xs italic text-zinc-400">Belum ada label</span>
              @endforelse
            </div>

            @if ($canManage)
              <div x-show="openMenu" x-collapse x-cloak class="mt-3 space-y-3">
                @if ($allLabels->isNotEmpty())
                  <div
                    class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 dark:border-zinc-700 dark:bg-zinc-900/50 custom-scrollbar">
                    @foreach ($allLabels as $label)
                      <button wire:click="toggleLabel({{ $label->id }})"
                        class="flex w-full items-center gap-3 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-white dark:hover:bg-zinc-800">
                        <div
                          class="flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors
                            {{ $task->labels->contains('id', $label->id) ? 'border-indigo-500 bg-indigo-500' : 'border-zinc-300 dark:border-zinc-600' }}">
                          @if ($task->labels->contains('id', $label->id))
                            <flux:icon name="check" class="size-3 text-white" />
                          @endif
                        </div>
                        <span class="h-3 w-3 shrink-0 rounded-full shadow-sm"
                          style="background-color: {{ $label->color }}"></span>
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $label->name }}</span>
                      </button>
                    @endforeach
                  </div>
                @endif

                @if (!$showLabelForm)
                  <button type="button" wire:click="$toggle('showLabelForm')"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-500 hover:border-indigo-400 hover:text-indigo-600 dark:border-zinc-600 dark:hover:border-indigo-500 dark:hover:text-indigo-400 transition-colors">
                    <flux:icon name="plus" class="size-3" /> Buat Label Baru
                  </button>
                @else
                  <form wire:submit="createLabel"
                    class="space-y-3 rounded-lg border border-indigo-100 bg-indigo-50/50 p-3 dark:border-indigo-900/30 dark:bg-indigo-900/10">
                    <flux:input wire:model="newLabelName" placeholder="Nama label..." size="sm" autofocus />
                    <div class="space-y-1.5">
                      <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Pilih Warna</span>
                      <div class="flex flex-wrap gap-2">
                        @foreach (['#6366f1', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6b7280'] as $color)
                          <button type="button" @click="$wire.set('newLabelColor', '{{ $color }}')"
                            class="h-5 w-5 rounded-full border-2 transition-transform hover:scale-110 focus:outline-none
                              {{ $newLabelColor === $color ? 'border-zinc-900 scale-110 shadow-sm dark:border-white' : 'border-transparent' }}"
                            style="background-color: {{ $color }}"></button>
                        @endforeach
                      </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                      <flux:button size="sm" variant="ghost" wire:click="$toggle('showLabelForm')">Batal
                      </flux:button>
                      <flux:button size="sm" variant="primary" type="submit">Simpan</flux:button>
                    </div>
                  </form>
                @endif
              </div>
            @endif
          </div>
        </div>

        {{-- Description --}}
        <div>
          <h3 class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Deskripsi / Catatan</h3>
          <flux:textarea wire:model.blur="taskDescription" wire:change="saveDescription" :readonly="$ro"
            rows="4"
            placeholder="{{ $canManage ? 'Tambahkan deskripsi rinci...' : 'Tidak ada deskripsi.' }}" />
        </div>

        {{-- Attachments --}}
        <div>
          <h3 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
            Lampiran
            @if ($attachments->isNotEmpty())
              <span class="ml-1 text-zinc-400">({{ $attachments->count() }})</span>
            @endif
          </h3>

          @if ($canManage)
            <div class="mb-3 grid grid-cols-2 gap-2" wire:loading.class="opacity-50 pointer-events-none"
              wire:target="uploadFiles">
              <label
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-500 transition-colors hover:border-zinc-400 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-zinc-500 dark:hover:text-zinc-300">
                <span wire:loading.remove wire:target="uploadFiles">
                  <flux:icon name="cloud-arrow-up" class="size-5" />
                </span>
                <span wire:loading wire:target="uploadFiles">
                  <flux:icon name="arrow-path" class="size-5 animate-spin" />
                </span>
                <span wire:loading.remove wire:target="uploadFiles">Upload file</span>
                <span wire:loading wire:target="uploadFiles" class="text-indigo-500">Uploading...</span>
                <input type="file" wire:model="uploadFiles" multiple class="hidden" />
              </label>
              <button type="button" wire:click="$toggle('showLinkForm')"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-500 transition-colors hover:border-zinc-400 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-zinc-500 dark:hover:text-zinc-300">
                <flux:icon name="link" class="size-5" />
                <span>Tambah link</span>
              </button>
            </div>

            @if ($showLinkForm)
              <div class="mb-3 space-y-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:input type="url" wire:model="newLinkUrl" placeholder="https://example.com"
                  size="sm" />
                <flux:input wire:model="newLinkLabel" placeholder="Label (opsional)" size="sm" />
                <div class="flex justify-end gap-2">
                  <flux:button size="xs" variant="ghost" wire:click="$set('showLinkForm', false)">Batal
                  </flux:button>
                  <flux:button size="xs" variant="primary" wire:click="addLinkAttachment">Simpan</flux:button>
                </div>
              </div>
            @endif
          @endif

          @forelse ($attachments as $attachment)
            <div
              class="group flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
              wire:key="attach-{{ $attachment->id }}">
              <a href="{{ $attachment->is_link ? $attachment->path : Storage::disk('public')->url($attachment->path) }}"
                target="_blank" rel="noopener noreferrer"
                class="flex min-w-0 items-center gap-2 text-zinc-700 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors">
                @if ($attachment->is_link)
                  <flux:icon name="link" class="size-4 shrink-0 text-indigo-400" />
                @elseif (str_starts_with($attachment->mime_type, 'image/'))
                  <flux:icon name="photo" class="size-4 shrink-0 text-indigo-400" />
                @else
                  <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
                @endif
                <span class="truncate text-sm">{{ $attachment->filename }}</span>
                @if (!$attachment->is_link)
                  <span class="shrink-0 text-xs text-zinc-400">{{ number_format($attachment->size / 1024, 1) }}
                    KB</span>
                @endif
                <flux:icon name="arrow-top-right-on-square" class="size-3.5 shrink-0 text-zinc-400" />
              </a>
              <div class="flex shrink-0 items-center gap-1">
                @if (!$attachment->is_link)
                  <a href="{{ Storage::disk('public')->url($attachment->path) }}"
                    download="{{ $attachment->filename }}" class="text-zinc-400 hover:text-indigo-500 transition-all"
                    title="Download">
                    <flux:icon name="arrow-down-tray" class="size-4" />
                  </a>
                @endif
                @if ($canManage)
                  <flux:button icon="trash" size="xs" variant="ghost"
                    wire:click="deleteAttachment({{ $attachment->id }})" class="text-red-500" />
                @endif
              </div>
            </div>
          @empty
            <p class="text-xs italic text-zinc-400">Belum ada lampiran.</p>
          @endforelse
        </div>
      </div>

      {{-- Checklist  --}}
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
            <flux:input wire:model="newChecklistName" placeholder="Nama checklist..." size="sm" class="flex-1"
              autofocus />
            <flux:button type="submit" size="sm" variant="primary">Tambah</flux:button>
            <flux:button type="button" size="sm" variant="ghost"
              wire:click="$set('showChecklistForm', false)">
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
                    class="flex-1 rounded-md border border-indigo-300 bg-white px-2 py-1 text-sm text-zinc-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                </div>
              </div>

              <div class="flex items-center gap-3 shrink-0">
                <span
                  class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $doneCount }}/{{ $totalItems }}</span>
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

                    <button
                      @if ($canManage) wire:click="toggleChecklistItem({{ $item->id }})" @else disabled @endif
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

                      <input x-show="editingItemId === {{ $item->id }}" x-cloak
                        x-ref="edit_cli_{{ $item->id }}" x-model="editTitle"
                        x-on:keydown.enter="$wire.editChecklistItemTitle({{ $item->id }}, editTitle); editingItemId = null"
                        x-on:keydown.escape="editingItemId = null" x-on:blur="editingItemId = null"
                        class="flex-1 rounded border border-indigo-300 bg-white px-2 py-0.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                    </div>

                    <div class="flex shrink-0 items-center gap-2 {{ $canManage ? '' : 'opacity-60' }}">

                      @if ($item->assignees->isNotEmpty())
                        <div class="flex -space-x-1.5">
                          @foreach ($item->assignees->take(3) as $a)
                            <flux:avatar circle :name="$a->name" :initials="$a->initials()"
                              :src="$a->avatar" size="xs" class="ring-2 ring-white dark:ring-zinc-900" />
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
                        <span
                          class="rounded-md border px-1.5 py-0.5 text-[11px] font-medium tracking-wide {{ $badgeClass }}">
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
                    <div
                      class="mx-3 mb-3 mt-1 space-y-4 rounded-lg border border-zinc-200/60 bg-white p-4 shadow-sm dark:border-zinc-700/50 dark:bg-zinc-800/80">

                      <div>
                        <label
                          class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Assign
                          Ke</label>
                        @if ($task->assignees->isEmpty())
                          <p class="text-xs text-zinc-400 italic">Belum ada anggota di task ini.</p>
                        @else
                          <div class="flex flex-wrap gap-2">
                            @foreach ($task->assignees as $member)
                              @php $checked = in_array($member->id, $activeItemAssigneeIds); @endphp
                              <label
                                class="flex cursor-pointer items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-medium transition-all duration-200
                                  {{ $checked ? 'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm dark:border-indigo-500 dark:bg-indigo-900/40 dark:text-indigo-200' : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                <input type="checkbox" wire:model="activeItemAssigneeIds"
                                  value="{{ $member->id }}" wire:change="updateChecklistItemAssignees"
                                  class="hidden" />
                                <flux:avatar circle :name="$member->name" :initials="$member->initials()"
                                  :src="$member->avatar" size="xs" class="size-5" />
                                {{ $member->name }}
                              </label>
                            @endforeach
                          </div>
                        @endif
                      </div>

                      <hr class="border-zinc-100 dark:border-zinc-700/50">

                      <div class="flex flex-wrap gap-6">
                        <div class="flex-1 min-w-[200px]">
                          <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tenggat
                            Waktu</label>
                          <div class="flex items-center gap-2">
                            <flux:input type="date" wire:model="activeItemDueDate" onclick="this.showPicker()"
                              wire:change="updateChecklistItemDueDate" size="sm" class="w-full max-w-[200px]" />
                            @if ($activeItemDueDate)
                              <button wire:click="clearChecklistItemDueDate"
                                class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors">Batal</button>
                            @endif
                          </div>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                          <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tambah
                            Lampiran</label>
                          <div class="grid grid-cols-2 gap-2" wire:loading.class="opacity-50 pointer-events-none"
                            wire:target="activeItemFiles">
                            <label
                              class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 py-2 text-xs font-medium text-zinc-600 transition-colors hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                              <span wire:loading.remove wire:target="activeItemFiles">
                                <flux:icon name="cloud-arrow-up" class="size-4" />
                              </span>
                              <span wire:loading wire:target="activeItemFiles">
                                <flux:icon name="arrow-path" class="size-4 animate-spin" />
                              </span>
                              <span wire:loading.remove wire:target="activeItemFiles">Upload File</span>
                              <span wire:loading wire:target="activeItemFiles">Uploading...</span>
                              <input type="file" wire:model="activeItemFiles" multiple class="hidden" />
                            </label>
                            <button type="button" wire:click="$toggle('showItemLinkForm')"
                              class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 py-2 text-xs font-medium text-zinc-600 transition-colors hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                              <flux:icon name="link" class="size-4" />
                              <span>Sematkan Link</span>
                            </button>
                          </div>
                        </div>
                      </div>

                      @if ($showItemLinkForm)
                        <div
                          class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-3 dark:border-indigo-900/30 dark:bg-indigo-900/10">
                          <div class="space-y-2">
                            <flux:input type="url" wire:model="newItemLinkUrl" placeholder="https://..."
                              size="sm" />
                            <flux:input wire:model="newItemLinkLabel" placeholder="Teks Label (Opsional)"
                              size="sm" />
                          </div>
                          <div class="mt-3 flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" wire:click="$set('showItemLinkForm', false)">
                              Batal</flux:button>
                            <flux:button size="sm" variant="primary" wire:click="addChecklistItemLink">Simpan
                              Link
                            </flux:button>
                          </div>
                        </div>
                      @endif

                      @if ($activeItemAttachments->isNotEmpty())
                        <div
                          class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 dark:border-zinc-700 dark:bg-zinc-800/30">
                          <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-wider text-zinc-400">Daftar
                            Lampiran ({{ $activeItemAttachments->count() }})</p>
                          <div class="space-y-1">
                            @foreach ($activeItemAttachments as $att)
                              <div
                                class="group flex items-center justify-between rounded-md px-2 py-1.5 transition-colors hover:bg-white dark:hover:bg-zinc-700 shadow-sm"
                                wire:key="cli-att-{{ $att->id }}">
                                <a href="{{ $att->is_link ? $att->path : Storage::disk('public')->url($att->path) }}"
                                  target="_blank" rel="noopener noreferrer"
                                  class="flex min-w-0 items-center gap-2 text-xs font-medium text-zinc-700 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors">
                                  @if ($att->is_link)
                                    <flux:icon name="link" class="size-4 shrink-0 text-indigo-500" />
                                  @elseif (str_starts_with($att->mime_type, 'image/'))
                                    <flux:icon name="photo" class="size-4 shrink-0 text-indigo-500" />
                                  @else
                                    <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
                                  @endif
                                  <span class="truncate">{{ $att->filename }}</span>
                                  @if (!$att->is_link)
                                    <span
                                      class="shrink-0 text-zinc-400 font-normal">{{ number_format($att->size / 1024, 1) }}
                                      KB</span>
                                  @endif
                                </a>
                                <div class="flex shrink-0 items-center gap-2 ml-3">
                                  @if (!$att->is_link)
                                    <a href="{{ Storage::disk('public')->url($att->path) }}"
                                      download="{{ $att->filename }}"
                                      class="text-zinc-400 hover:text-indigo-600 transition-colors" title="Download">
                                      <flux:icon name="arrow-down-tray" class="size-4" />
                                    </a>
                                  @endif
                                  <button wire:click="deleteChecklistItemAttachment({{ $att->id }})"
                                    class="text-zinc-400 hover:text-red-500 transition-colors" title="Hapus Lampiran">
                                    <flux:icon name="trash" class="size-4" />
                                  </button>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        </div>
                      @endif

                      <div class="mt-4 flex justify-end border-t border-zinc-100 pt-3 dark:border-zinc-700/50">
                        <flux:button size="sm" variant="ghost" wire:click="closeChecklistItemPanel">Selesai
                        </flux:button>
                      </div>
                    </div>
                  @endif
                </div>
              @endforeach
            </div>

            @if ($canManage)
              <div class="mt-3">
                @if ($addingItemToChecklistId === $checklist->id)
                  <form wire:submit="addChecklistItem"
                    class="flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50/50 p-2 dark:border-indigo-900/50 dark:bg-indigo-900/10">
                    <flux:input wire:model="newChecklistItemTitle" placeholder="Judul sub tugas baru..."
                      size="sm" class="flex-1 bg-white dark:bg-zinc-900" autofocus />
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

      {{-- Comments  --}}
      <div x-show="activeTab === 'comments'" x-cloak>
        <form wire:submit="addComment" class="mb-4">
          <flux:textarea wire:model="newComment" placeholder="Tambahkan komentar... (Ctrl+Enter untuk kirim)"
            rows="3" x-on:keydown.ctrl.enter.prevent="$wire.addComment()"
            x-on:keydown.meta.enter.prevent="$wire.addComment()" />
          <div class="mt-2 flex items-center justify-between">
            <span class="text-[10px] text-zinc-400 dark:text-zinc-500">Tekan <kbd
                class="rounded bg-zinc-100 px-1 py-0.5 text-[9px] font-mono dark:bg-zinc-800">Ctrl+Enter</kbd> untuk
              kirim</span>
            <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane">Kirim</flux:button>
          </div>
        </form>

        @forelse ($comments as $comment)
          <div class="mb-3 rounded-lg border border-zinc-100 p-3 dark:border-zinc-700"
            wire:key="comment-{{ $comment->id }}">
            <div class="mb-2 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <flux:avatar circle :name="$comment->user?->name ?? 'Unknown'"
                  :initials="$comment->user?->initials() ?? '?'" :src="$comment->user?->avatar" size="xs" />
                <span
                  class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $comment->user?->name ?? 'Unknown' }}</span>
                <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
              </div>
              <div class="flex items-center gap-1">
                <button wire:click="startReply({{ $comment->id }})"
                  class="flex items-center gap-1 rounded-md px-2 py-1 text-xs text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-indigo-400"
                  title="Balas">
                  <flux:icon name="arrow-uturn-left" class="size-3.5" />
                  Balas
                </button>
                @if ($comment->user_id === auth()->id())
                  <flux:button icon="trash" size="xs" variant="ghost"
                    wire:click="deleteComment({{ $comment->id }})" class="text-red-500" />
                @endif
              </div>
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $comment->body }}</p>

            @if ($comment->replies->isNotEmpty())
              <div class="mt-3 ml-6 space-y-3 border-l-2 border-zinc-200 pl-3 dark:border-zinc-600">
                @foreach ($comment->replies as $reply)
                  <div wire:key="reply-{{ $reply->id }}">
                    <div class="flex items-center justify-between gap-2 mb-1">
                      <div class="flex items-center gap-2">
                        <flux:avatar circle :name="$reply->user?->name ?? 'Unknown'"
                          :initials="$reply->user?->initials() ?? '?'" :src="$reply->user?->avatar" size="xs" />
                        <span
                          class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ $reply->user?->name ?? 'Unknown' }}</span>
                        <span class="text-[10px] text-zinc-400">{{ $reply->created_at->diffForHumans() }}</span>
                      </div>
                      @if ($reply->user_id === auth()->id())
                        <button wire:click="deleteReply({{ $reply->id }})"
                          class="rounded p-1 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                          title="Hapus Balasan">
                          <flux:icon name="trash" class="size-3" />
                        </button>
                      @endif
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-pre-wrap">{{ $reply->body }}</p>
                  </div>
                @endforeach
              </div>
            @endif

            @if ($replyingToCommentId === $comment->id)
              <form wire:submit="addReply"
                class="mt-3 ml-6 space-y-2 rounded-lg border border-indigo-200 bg-indigo-50/30 p-2 dark:border-indigo-900/40 dark:bg-indigo-900/10">
                <flux:textarea wire:model="replyBody" placeholder="Tulis balasan..." rows="2"
                  x-on:keydown.ctrl.enter.prevent="$wire.addReply()"
                  x-on:keydown.meta.enter.prevent="$wire.addReply()" x-on:keydown.escape="$wire.cancelReply()"
                  autofocus />
                <div class="flex justify-end gap-2">
                  <flux:button type="button" size="xs" variant="ghost" wire:click="cancelReply">Batal
                  </flux:button>
                  <flux:button type="submit" size="xs" variant="primary">Kirim Balasan</flux:button>
                </div>
              </form>
            @endif
          </div>
        @empty
          <div
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 px-4 py-10 text-center dark:border-zinc-700/60">
            <flux:icon name="chat-bubble-left" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Belum ada komentar.</p>
            <p class="mt-1 text-xs text-zinc-400">Jadilah yang pertama berkomentar.</p>
          </div>
        @endforelse
      </div>

      {{-- Activity  --}}
      <div x-show="activeTab === 'activity'" x-cloak class="space-y-6">
        {{-- Time tracking --}}
        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700/50 dark:bg-zinc-800/30">
          <div class="mb-3 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <flux:icon name="clock" class="size-4 text-zinc-400" />
              <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Time Tracking</h3>
            </div>

            <div
              class="flex items-center gap-1.5 rounded-md bg-white px-2 py-1 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
              <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Total</span>
              <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                {{ floor($totalTimeSeconds / 3600) }}h {{ floor(($totalTimeSeconds % 3600) / 60) }}m
              </span>
            </div>
          </div>

          @if ($timeEntries->isNotEmpty())
            <div class="space-y-2">
              <h4 class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Riwayat Sesi — Semua Anggota
              </h4>

              <div
                class="max-h-56 overflow-y-auto rounded-lg border border-zinc-200 bg-white custom-scrollbar dark:border-zinc-700 dark:bg-zinc-900/50">
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                  @foreach ($timeEntries as $entry)
                    <div
                      class="flex items-center justify-between gap-3 px-3 py-2.5 text-xs transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                      <div class="flex items-center gap-2.5 min-w-0">
                        <flux:avatar circle :name="$entry->user?->name ?? 'Unknown'"
                          :initials="$entry->user?->initials() ?? '?'" :src="$entry->user?->avatar" size="xs"
                          class="size-6 shrink-0 ring-2 ring-white dark:ring-zinc-900" />
                        <div class="min-w-0">
                          <span class="block truncate font-medium text-zinc-700 dark:text-zinc-200">
                            {{ $entry->user?->name ?? 'Unknown' }}
                          </span>
                          <span class="block text-[10px] text-zinc-400 dark:text-zinc-500">
                            {{ $entry->started_at->format('d M Y, H:i') }}
                            @if ($entry->stopped_at)
                              — {{ $entry->stopped_at->format('H:i') }}
                            @endif
                          </span>
                        </div>
                      </div>

                      <span class="shrink-0">
                        @if ($entry->stopped_at)
                          <span
                            class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            <flux:icon name="clock" class="size-3 text-zinc-400" />
                            {{ floor($entry->duration_seconds / 3600) }}jam
                            {{ floor(($entry->duration_seconds % 3600) / 60) }}menit
                          </span>
                        @else
                          <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                            <span class="size-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            Berjalan
                          </span>
                        @endif
                      </span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @else
            <p class="text-xs italic text-zinc-400 dark:text-zinc-500">
              Belum ada sesi pencatatan waktu untuk tugas ini.
            </p>
          @endif
        </div>

        {{-- Activity log --}}
        <div x-data="{ expanded: {{ $activities->count() <= 5 ? 'true' : 'false' }} }">
          <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
              Riwayat Aktivitas
              @if ($activities->count() > 0)
                <span class="ml-1 text-xs text-zinc-400">({{ $activities->count() }})</span>
              @endif
            </h3>
            @if ($activities->count() > 5)
              <button @click="expanded = !expanded"
                class="flex items-center gap-1 rounded-md px-2 py-1 text-xs text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
                <span x-text="expanded ? 'Tutup' : 'Tampilkan semua'"></span>
                <flux:icon name="chevron-down" class="size-3.5 transition-transform"
                  x-bind:class="expanded ? 'rotate-180' : ''" />
              </button>
            @endif
          </div>
          @if ($activities->isNotEmpty())
            <div class="space-y-2">
              @foreach ($activities as $index => $activity)
                <div class="flex items-start gap-2 text-xs" wire:key="activity-{{ $activity->id }}"
                  @if ($index >= 5) x-show="expanded" x-cloak x-collapse @endif>
                  <flux:avatar circle :name="$activity->user?->name ?? 'Unknown'"
                    :initials="$activity->user?->initials() ?? '?'" :src="$activity->user?->avatar" size="xs"
                    class="mt-0.5" />
                  <div>
                    <span
                      class="font-medium text-zinc-700 dark:text-zinc-300">{{ $activity->user?->name ?? 'Unknown' }}</span>
                    <span class="text-zinc-500 dark:text-zinc-400">
                      @switch($activity->type)
                        @case('created')
                          membuat tugas ini
                        @break

                        @case('status_changed')
                          mengubah status dari <span class="font-medium">{{ $activity->old_value }}</span>
                          ke <span class="font-medium">{{ $activity->new_value }}</span>
                        @break

                        @case('priority_changed')
                          mengubah prioritas dari <span class="font-medium">{{ $activity->old_value }}</span>
                          ke <span class="font-medium">{{ $activity->new_value }}</span>
                        @break

                        @case('assignee_changed')
                          menugaskan ke <span class="font-medium">{{ $activity->new_value }}</span>
                        @break

                        @default
                          {{ $activity->type }}
                      @endswitch
                    </span>
                    <span
                      class="block text-zinc-400 dark:text-zinc-500">{{ $activity->created_at->diffForHumans() }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-xs italic text-zinc-400 dark:text-zinc-500">Belum ada aktivitas.</p>
          @endif
        </div>
      </div>
    </div>

    <div class="mt-6 border-t border-zinc-100 pt-3 text-xs text-zinc-400 dark:border-zinc-700/50 dark:text-zinc-500">
      Dibuat oleh {{ $task->creator?->name ?? 'Unknown' }} ·
      {{ $task->created_at->format('d M Y \\p\\u\\k\\u\\l H:i') }}
    </div>
  @else
    <div class="flex items-center justify-center py-12">
      <p class="text-zinc-500">Tugas tidak ditemukan</p>
    </div>
  @endif
</div>
