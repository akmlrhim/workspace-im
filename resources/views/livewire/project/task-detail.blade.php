@php $ro = !$canManage; @endphp
<div class="space-y-6">
  @if ($task)
    @if ($ro)
      <div
        class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-400">
        <flux:icon name="lock-closed" class="size-4 shrink-0" />
        <span>Anda sedang melihat tugas ini dalam mode <strong>lihat saja</strong>. Anda tidak ditugaskan untuk tugas
          ini.</span>
      </div>
    @endif
    <div class="space-y-4">
      <div class="flex items-start justify-between gap-3 pr-8 sm:pr-10">
        <div class="flex-1 min-w-0">
          <flux:input wire:model.blur="taskTitle" wire:change="saveTitle" :readonly="$ro" class="text-xl font-bold"
            placeholder="Judul tugas..." />
        </div>
        @if ($canManage)
          <div class="flex shrink-0 items-center pt-1">
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
              <flux:avatar :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar"
                size="xs" />
              {{ $assignee->name }}
            </span>
          @empty
            <span class="text-xs text-zinc-400">No assignees</span>
          @endforelse
        </div>

        @if ($workspaceUsers->isEmpty())
          <div
            class="flex items-center gap-2 rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-xs text-zinc-400 dark:border-zinc-700">
            <flux:icon name="users" class="size-4 shrink-0" />
            <span>Belum ada anggota di list ini. Tambahkan anggota melalui halaman Space terlebih dahulu.</span>
          </div>
        @else
          <div
            class="max-h-32 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700 {{ $ro ? 'opacity-60' : '' }}">
            @foreach ($workspaceUsers as $member)
              <label
                class="flex items-center gap-2 rounded-md px-2 py-1 text-sm {{ $canManage ? 'hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer' : 'cursor-not-allowed' }}">
                <flux:checkbox wire:model="taskAssigneeIds" :value="$member->id" wire:change="updateAssignees"
                  :disabled="$ro" />
                <flux:avatar :name="$member->name" :initials="$member->initials()" :src="$member->avatar"
                  size="xs" />
                <span class="text-zinc-700 dark:text-zinc-300">{{ $member->name }}</span>
              </label>
            @endforeach
          </div>
        @endif
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
                  <button type="button" @click="$wire.set('newLabelColor', '{{ $color }}')"
                    class="h-4 w-4 rounded-full border-2 transition-transform hover:scale-125 {{ $newLabelColor === $color ? 'border-zinc-900 dark:border-white scale-125' : 'border-transparent' }}"
                    style="background-color: {{ $color }}"></button>
                @endforeach
              </div>
            </div>
            <div class="flex justify-end gap-1">
              <flux:button size="xs" variant="ghost" @click="$wire.set('showLabelForm', false)">Batal</flux:button>
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
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Ceklist</h3>
        @if ($canManage)
          <flux:button icon="plus" size="xs" variant="ghost" wire:click="$toggle('showChecklistForm')">
            Tambah Ceklist
          </flux:button>
        @endif
      </div>

      @if ($canManage && $showChecklistForm)
        <form wire:submit="addChecklist" class="mb-4 flex gap-2">
          <flux:input wire:model="newChecklistName" placeholder="Nama ceklis..." size="sm" class="flex-1"
            autofocus />
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

        <div class="mb-5" wire:key="cl-{{ $checklist->id }}">
          <div class="mb-1 flex items-center gap-2" x-data="{ editing: false, name: '{{ addslashes($checklist->name) }}' }">
            <flux:icon name="check-circle" class="size-4 shrink-0 text-indigo-500" />

            <span x-show="!editing"
              @if ($canManage) x-on:dblclick="editing = true; $nextTick(() => $refs['cl_name_{{ $checklist->id }}'].focus())" @endif
              class="flex-1 text-sm font-semibold text-zinc-700 dark:text-zinc-300 {{ $canManage ? 'cursor-text' : '' }}">
              {{ $checklist->name }}
            </span>

            <input x-show="editing" x-cloak x-ref="cl_name_{{ $checklist->id }}" x-model="name"
              x-on:keydown.enter="$wire.editChecklistName({{ $checklist->id }}, name); editing = false"
              x-on:keydown.escape="editing = false" x-on:blur="editing = false"
              class="flex-1 rounded border border-zinc-300 bg-white px-2 py-0.5 text-sm text-zinc-700 focus:outline-none focus:ring-1 focus:ring-indigo-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />

            <span class="text-xs text-zinc-400">{{ $doneCount }}/{{ $totalItems }}</span>

            @if ($canManage)
              <button wire:click="deleteChecklist({{ $checklist->id }})"
                class="text-zinc-300 hover:text-red-500 dark:text-zinc-600 dark:hover:text-red-400 transition-colors">
                <flux:icon name="trash" class="size-3.5" />
              </button>
            @endif
          </div>

          <div class="mb-2 h-1.5 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
            <div
              class="h-full rounded-full transition-all duration-300 {{ $pct == 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
              style="width: {{ $pct }}%"></div>
          </div>

          <div class="space-y-0.5" x-data="{ editingItemId: null, editTitle: '' }">
            @foreach ($allItems as $item)
              @php $isActive = $activeChecklistItemId === $item->id; @endphp
              <div wire:key="cli-{{ $item->id }}"
                class="rounded-lg border {{ $isActive ? 'border-indigo-200 dark:border-indigo-800' : 'border-transparent' }}">

                <div
                  class="group flex items-center gap-2 px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 rounded-lg">
                  <button
                    @if ($canManage) wire:click="toggleChecklistItem({{ $item->id }})" @else disabled @endif
                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition-colors
                      {{ $item->is_completed ? 'border-green-500 bg-green-500' : 'border-zinc-300 dark:border-zinc-600' }}
                      {{ $canManage ? 'hover:border-indigo-400' : 'cursor-not-allowed opacity-60' }}">
                    @if ($item->is_completed)
                      <flux:icon name="check" class="size-3 text-white stroke-[3]" />
                    @endif
                  </button>

                  <span x-show="editingItemId !== {{ $item->id }}"
                    @if ($canManage) x-on:dblclick="editingItemId = {{ $item->id }}; editTitle = '{{ addslashes($item->title) }}'; $nextTick(() => $refs['edit_cli_{{ $item->id }}'].focus())" @endif
                    class="flex-1 text-sm {{ $item->is_completed ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-700 dark:text-zinc-300' }} {{ $canManage ? 'cursor-text' : '' }}">
                    {{ $item->title }}
                  </span>
                  <input x-show="editingItemId === {{ $item->id }}" x-cloak x-ref="edit_cli_{{ $item->id }}"
                    x-model="editTitle"
                    x-on:keydown.enter="$wire.editChecklistItemTitle({{ $item->id }}, editTitle); editingItemId = null"
                    x-on:keydown.escape="editingItemId = null" x-on:blur="editingItemId = null"
                    class="flex-1 rounded border border-zinc-300 bg-white px-2 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />

                  <div class="flex shrink-0 items-center gap-1 {{ $canManage ? '' : 'opacity-60' }}">
                    @if ($canManage)
                      <button wire:click="openChecklistItemPanel({{ $item->id }})"
                        class="flex items-center gap-0.5 rounded px-1 py-0.5 text-xs transition-colors {{ $isActive ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400' : 'text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
                        title="Kelola assignee, tanggal &amp; lampiran">
                        <flux:icon name="ellipsis-horizontal" class="size-4" />
                      </button>
                    @endif

                    @if ($item->assignees->isNotEmpty())
                      <div class="flex -space-x-1">
                        @foreach ($item->assignees->take(3) as $a)
                          <flux:avatar :name="$a->name" :initials="$a->initials()" :src="$a->avatar"
                            size="xs" class="ring-1 ring-white dark:ring-zinc-800" />
                        @endforeach
                      </div>
                    @endif

                    @if ($item->attachments->isNotEmpty())
                      <span class="flex items-center gap-0.5 text-xs text-zinc-400">
                        <flux:icon name="paper-clip" class="size-3.5" />
                        {{ $item->attachments->count() }}
                      </span>
                    @endif

                    @if ($item->due_date)
                      @php
                        $badgeClass = $item->is_completed
                            ? 'bg-green-500 text-white'
                            : ($item->due_date->isPast()
                                ? 'bg-red-500 text-white'
                                : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300');
                      @endphp
                      <span class="rounded px-1.5 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                        {{ $item->due_date->format('d M') }}
                        @if ($item->is_completed)
                          ✓
                        @endif
                      </span>
                    @endif

                    @if ($canManage)
                      <button wire:click="deleteChecklistItem({{ $item->id }})"
                        class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                        <flux:icon name="trash" class="size-3.5" />
                      </button>
                    @endif
                  </div>
                </div>

                @if ($isActive)
                  <div
                    class="mx-2 mb-2 space-y-3 rounded-lg border border-zinc-100 bg-zinc-50/80 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">

                    <div>
                      <p class="mb-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Assign ke</p>
                      @if ($task->assignees->isEmpty())
                        <p class="text-xs text-zinc-400">Belum ada anggota di task ini.</p>
                      @else
                        <div class="flex flex-wrap gap-1.5">
                          @foreach ($task->assignees as $member)
                            @php $checked = in_array($member->id, $activeItemAssigneeIds); @endphp
                            <label
                              class="flex cursor-pointer items-center gap-1.5 rounded-full border px-2 py-1 text-xs transition-colors
                              {{ $checked ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-600 dark:text-zinc-400' }}">
                              <input type="checkbox" wire:model="activeItemAssigneeIds" value="{{ $member->id }}"
                                wire:change="updateChecklistItemAssignees" class="hidden" />
                              <flux:avatar :name="$member->name" :initials="$member->initials()"
                                :src="$member->avatar" size="xs" />
                              {{ $member->name }}
                            </label>
                          @endforeach
                        </div>
                      @endif
                    </div>

                    <div>
                      <p class="mb-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Tanggal</p>
                      <div class="flex items-center gap-2">
                        <flux:input type="date" wire:model="activeItemDueDate"
                          wire:change="updateChecklistItemDueDate" size="sm" class="w-44" />
                        @if ($activeItemDueDate)
                          <flux:button size="xs" variant="ghost" wire:click="clearChecklistItemDueDate"
                            class="text-red-500">Hapus</flux:button>
                        @endif
                      </div>
                    </div>

                    <div>
                      <p class="mb-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        Lampiran
                        @if ($activeItemAttachments->isNotEmpty())
                          <span class="text-zinc-400">({{ $activeItemAttachments->count() }})</span>
                        @endif
                      </p>

                      <div wire:loading.class="opacity-50 pointer-events-none" wire:target="activeItemFiles">
                        <label
                          class="mb-2 flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-dashed border-zinc-300 px-3 py-2 text-xs text-zinc-500 transition-colors hover:border-indigo-400 hover:text-indigo-600 dark:border-zinc-600">
                          <span wire:loading.remove wire:target="activeItemFiles">
                            <flux:icon name="cloud-arrow-up" class="size-4" />
                          </span>
                          <span wire:loading wire:target="activeItemFiles">
                            <flux:icon name="arrow-path" class="size-4 animate-spin" />
                          </span>
                          <span wire:loading.remove wire:target="activeItemFiles">Pilih file (multi)</span>
                          <span wire:loading wire:target="activeItemFiles" class="text-indigo-500">Uploading...</span>
                          <input type="file" wire:model="activeItemFiles" multiple class="hidden" />
                        </label>
                      </div>

                      @if ($activeItemAttachments->isNotEmpty())
                        <div class="mt-2 space-y-1">
                          @foreach ($activeItemAttachments as $att)
                            <div
                              class="group flex items-center justify-between rounded-md px-2 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                              wire:key="cli-att-{{ $att->id }}">
                              <a href="{{ Storage::disk('public')->url($att->path) }}" target="_blank"
                                class="flex min-w-0 items-center gap-1.5 text-xs text-zinc-600 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors">
                                @if (str_starts_with($att->mime_type, 'image/'))
                                  <flux:icon name="photo" class="size-3.5 shrink-0 text-indigo-400" />
                                @else
                                  <flux:icon name="document" class="size-3.5 shrink-0 text-zinc-400" />
                                @endif
                                <span class="truncate">{{ $att->filename }}</span>
                                <span class="shrink-0 text-zinc-400">{{ number_format($att->size / 1024, 1) }}
                                  KB</span>
                                <flux:icon name="arrow-top-right-on-square"
                                  class="size-3 shrink-0 opacity-0 group-hover:opacity-100" />
                              </a>
                              <div class="flex shrink-0 items-center gap-1 ml-2">
                                <a href="{{ Storage::disk('public')->url($att->path) }}"
                                  download="{{ $att->filename }}"
                                  class="text-zinc-400 hover:text-indigo-500 transition-colors" title="Download">
                                  <flux:icon name="arrow-down-tray" class="size-3.5" />
                                </a>
                                <button wire:click="deleteChecklistItemAttachment({{ $att->id }})"
                                  class="text-zinc-400 hover:text-red-500 transition-colors" title="Hapus">
                                  <flux:icon name="trash" class="size-3.5" />
                                </button>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      @endif
                    </div>

                    <div class="flex justify-end">
                      <flux:button size="xs" variant="ghost" wire:click="closeChecklistItemPanel">Tutup
                      </flux:button>
                    </div>
                  </div>
                @endif
              </div>
            @endforeach
          </div>

          @if ($canManage)
            @if ($addingItemToChecklistId === $checklist->id)
              <form wire:submit="addChecklistItem" class="mt-2 flex gap-2 pl-2">
                <flux:input wire:model="newChecklistItemTitle" placeholder="Judul sub tugas..." size="sm"
                  class="flex-1" autofocus />
                <flux:button type="submit" size="sm" variant="primary">Tambah</flux:button>
                <flux:button type="button" size="sm" variant="ghost"
                  wire:click="openAddChecklistItem({{ $checklist->id }})">Batal</flux:button>
              </form>
            @else
              <button wire:click="openAddChecklistItem({{ $checklist->id }})"
                class="mt-1.5 flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-zinc-300 py-1.5 text-xs text-zinc-500 transition-colors hover:border-zinc-400 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-zinc-500 dark:hover:text-zinc-300">
                <flux:icon name="plus" class="size-3.5" />
                Tambah Sub Tugas
              </button>
            @endif
          @endif
        </div>
      @empty
        @if (!$showChecklistForm)
          <p class="text-xs text-zinc-400">Belum ada ceklis. Klik "Tambah Ceklis" untuk memulai.</p>
        @endif
      @endforelse
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
        <div class="mb-3" wire:loading.class="opacity-50 pointer-events-none" wire:target="uploadFiles">
          <label
            class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-500 transition-colors hover:border-zinc-400 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-zinc-500 dark:hover:text-zinc-300">
            <span wire:loading.remove wire:target="uploadFiles">
              <flux:icon name="cloud-arrow-up" class="size-5" />
            </span>
            <span wire:loading wire:target="uploadFiles">
              <flux:icon name="arrow-path" class="size-5 animate-spin" />
            </span>
            <span wire:loading.remove wire:target="uploadFiles">Upload file (multi)</span>
            <span wire:loading wire:target="uploadFiles" class="text-indigo-500">Uploading...</span>
            <input type="file" wire:model="uploadFiles" multiple class="hidden" />
          </label>
        </div>
      @endif

      @forelse ($attachments as $attachment)
        <div
          class="group flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
          wire:key="attach-{{ $attachment->id }}">
          <a href="{{ Storage::disk('public')->url($attachment->path) }}" target="_blank"
            class="flex min-w-0 items-center gap-2 text-zinc-700 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors">
            @if (str_starts_with($attachment->mime_type, 'image/'))
              <flux:icon name="photo" class="size-4 shrink-0 text-indigo-400" />
            @else
              <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
            @endif
            <span class="truncate text-sm">{{ $attachment->filename }}</span>
            <span class="shrink-0 text-xs text-zinc-400">{{ number_format($attachment->size / 1024, 1) }} KB</span>
            <flux:icon name="arrow-top-right-on-square" class="size-3.5 shrink-0 text-zinc-400" />
          </a>
          <div class="flex shrink-0 items-center gap-1">
            <a href="{{ Storage::disk('public')->url($attachment->path) }}" download="{{ $attachment->filename }}"
              class="text-zinc-400 hover:text-indigo-500 transition-all" title="Download">
              <flux:icon name="arrow-down-tray" class="size-4" />
            </a>
            @if ($canManage)
              <flux:button icon="trash" size="xs" variant="ghost"
                wire:click="deleteAttachment({{ $attachment->id }})" class="text-red-500" />
            @endif
          </div>
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
                <flux:avatar :name="$comment->user->name" :initials="$comment->user->initials()"
                  :src="$comment->user->avatar" size="xs" />
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
                      <flux:avatar :name="$reply->user->name" :initials="$reply->user->initials()"
                        :src="$reply->user->avatar" size="xs" />
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
            <flux:avatar :name="$activity->user->name" :initials="$activity->user->initials()"
              :src="$activity->user->avatar" size="xs" class="mt-0.5" />
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
