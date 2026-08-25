@php
$taskLog = $dt->logs->firstWhere('user_id', $myId);
$isDone = (bool) $taskLog?->is_completed;
$completedLogs = $dt->logs->where('is_completed', true)->values();
$otherCompletedLogs = $completedLogs->where('user_id', '!=', $myId)->take(4);
@endphp

<div wire:key="task-{{ $dt->id }}-{{ $selectedDate }}-{{ $isDone ? 1 : 0 }}"
    x-show="!deletingIds.includes({{ $dt->id }})" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1"
    x-data="{
    editing: false,
    done: @js($isDone),
    doneAt: @js($taskLog?->completed_at?->format('H:i')),
    pending: 0,
    t: @js($dt->title),
    d: @js($dt->description ?? ''),
    openEdit() {
        this.editing = true;
        this.$nextTick(() => this.$refs.editInput?.focus())
    },
    cancelEdit() {
        this.editing = false;
        this.t = @js($dt->title);
        this.d = @js($dt->description ?? '')
    },
    submitEdit() {
        if (!this.t.trim()) return;
        this.$wire.saveEdit({{ $dt->id }}, this.t.trim(), this.d.trim());
        this.editing = false;
    },
    applyDone(isDone, at) {
        if (isDone === this.done) return;
        this.done = isDone;
        this.doneAt = at;
        this.$dispatch('daily-task-toggled', { delta: isDone ? 1 : -1 });
    },
    nowLabel() {
        return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    },
    async toggleDone() {
        const completing = !this.done;
        const previousDoneAt = this.doneAt;
        this.applyDone(completing, completing ? this.nowLabel() : null);
        this.pending++;
        try {
            const serverDone = await this.$wire.toggleComplete({{ $dt->id }});
            if (serverDone === null || serverDone === undefined) {
                this.applyDone(!completing, previousDoneAt);
            } else {
                this.applyDone(serverDone, serverDone ? (this.doneAt ?? this.nowLabel()) : null);
            }
        } catch (e) {
            this.applyDone(!completing, previousDoneAt);
        } finally {
            this.pending--;
        }
    }
}"
    data-done="{{ $isDone ? 'true' : 'false' }}" data-busy="false" data-editing="false"
    :data-done="done ? 'true' : 'false'" :data-busy="pending > 0 ? 'true' : 'false'"
    :data-editing="editing ? 'true' : 'false'"
    class="group/task flex items-start gap-3 px-4 py-3.5 transition-colors data-[done=true]:bg-emerald-50/50 dark:data-[done=true]:bg-emerald-400/5">

    @if ($canManage)
    <button type="button" role="checkbox" :aria-checked="done" @click="toggleDone()"
        :title="done ? 'Tandai belum selesai' : 'Tandai selesai'"
        class="relative mt-0.5 grid h-[22px] w-[22px] shrink-0 cursor-pointer place-items-center rounded-md border-2 border-zinc-300 transition-all duration-150 before:absolute before:-inset-2 before:content-[''] hover:border-emerald-400 active:scale-90 group-data-[done=true]/task:border-emerald-500 group-data-[done=true]/task:bg-emerald-500 dark:border-zinc-600 dark:hover:border-emerald-500 dark:group-data-[done=true]/task:border-emerald-500">
        <svg
            class="h-3.5 w-3.5 scale-50 text-white opacity-0 transition-all duration-200 group-data-[done=true]/task:scale-100 group-data-[done=true]/task:opacity-100 group-data-[busy=true]/task:opacity-0"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 13l4 4L19 7" />
        </svg>
        <svg
            class="absolute h-3.5 w-3.5 animate-spin text-zinc-400 opacity-0 transition-opacity group-data-[busy=true]/task:opacity-100 group-data-[done=true]/task:text-white dark:text-zinc-500"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    </button>
    @else
    <div
        class="mt-0.5 grid h-[22px] w-[22px] shrink-0 place-items-center rounded-md border-2 border-zinc-300 group-data-[done=true]/task:border-emerald-500 group-data-[done=true]/task:bg-emerald-500 dark:border-zinc-600 dark:group-data-[done=true]/task:border-emerald-500">
        <svg
            class="h-3.5 w-3.5 scale-50 text-white opacity-0 transition-all duration-200 group-data-[done=true]/task:scale-100 group-data-[done=true]/task:opacity-100"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 13l4 4L19 7" />
        </svg>
    </div>
    @endif

    <div class="min-w-0 flex-1">
        <div class="group-data-[editing=true]/task:hidden">
            <p
                class="text-sm font-medium leading-snug text-zinc-800 transition-colors group-data-[done=true]/task:text-zinc-400 group-data-[done=true]/task:line-through dark:text-zinc-100 dark:group-data-[done=true]/task:text-zinc-500">
                {{ $dt->title }}
            </p>

            @if ($dt->description)
            <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">{{ $dt->description }}</p>
            @endif

            <div
                class="mt-1.5 items-center gap-1.5 {{ $otherCompletedLogs->isNotEmpty() ? 'flex' : 'hidden group-data-[done=true]/task:flex' }}">
                <div class="flex -space-x-1.5">
                    <span class="hidden group-data-[done=true]/task:inline-flex">
                        <flux:avatar circle :name="auth()->user()->name" :src="auth()->user()->avatar" size="xs"
                            class="ring-1 ring-white dark:ring-zinc-900" />
                    </span>
                    @foreach ($otherCompletedLogs as $log)
                    <flux:avatar circle :name="$log->user?->name ?? '?'" :src="$log->user?->avatar ?? null"
                        size="xs" class="ring-1 ring-white dark:ring-zinc-900" />
                    @endforeach
                </div>
            </div>

            @if ($taskLog?->reason)
            <div
                class="mt-1.5 inline-flex items-start gap-1 rounded-md bg-amber-50 px-2 py-1 group-data-[done=true]/task:hidden dark:bg-amber-900/20">
                <flux:icon.chat-bubble-left-ellipsis class="mt-0.5 h-3 w-3 shrink-0 text-amber-500" />
                <span
                    class="text-xs leading-relaxed text-amber-700 dark:text-amber-400">{{ $taskLog->reason }}</span>
            </div>
            @endif

            <div class="mt-1 hidden items-center gap-1 group-data-[done=true]/task:flex">
                <flux:icon name="clock" class="size-3 text-emerald-500" />
                <span class="text-xs text-emerald-600 dark:text-emerald-400"
                    x-text="doneAt ? `Selesai ${doneAt}` : ''">{{ $taskLog?->completed_at ? 'Selesai ' . $taskLog->completed_at->format('H:i') : '' }}</span>
            </div>
        </div>

        @if ($canManage)
        <div class="hidden space-y-2 group-data-[editing=true]/task:block">
            <input x-ref="editInput" x-model="t" @keydown.enter.prevent="submitEdit()"
                @keydown.escape="cancelEdit()"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
            <input x-model="d" @keydown.escape="cancelEdit()" placeholder="Deskripsi (opsional)..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
            <div class="flex gap-2">
                <flux:button @click="submitEdit()" variant="primary" size="sm" wire:loading.attr="disabled"
                    wire:loading.class="opacity-75" wire:target="saveEdit">
                    Simpan
                </flux:button>
                <flux:button @click="cancelEdit()" variant="ghost" size="sm">Batal</flux:button>
            </div>
        </div>
        @endif
    </div>

    @if ($canManage)
    <div
        class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity focus-within:opacity-100 group-hover/task:opacity-100 group-data-[editing=true]/task:hidden max-sm:opacity-100">
        <div class="group-data-[done=true]/task:hidden">
            <flux:tooltip content="Catat alasan" position="top">
                <flux:button variant="ghost" size="sm" icon="chat-bubble-left-ellipsis"
                    class="h-7 w-7 p-0 text-zinc-400 hover:text-amber-500"
                    wire:click="openReasonModal({{ $dt->id }})" wire:loading.attr="disabled"
                    wire:target="openReasonModal({{ $dt->id }})" />
            </flux:tooltip>
        </div>
        <flux:dropdown position="bottom" align="end">
            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                class="h-7 w-7 p-0 text-zinc-400" />
            <flux:menu>
                <flux:menu.item icon="pencil-square" @click="openEdit()">Edit</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item variant="danger" icon="trash"
                    @click="pendingDeleteId = {{ $dt->id }}; deleteModal = true">
                    Hapus
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>
    @endif

</div>
