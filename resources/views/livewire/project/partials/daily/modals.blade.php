{{-- Delete modal --}}
<div x-show="deleteModal" x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="deleteModal = false" @keydown.escape.window="deleteModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none">
    <div x-show="deleteModal" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full max-w-sm rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Hapus Daily Task?</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tindakan ini tidak bisa dibatalkan.</p>
        <div class="mt-5 flex justify-end gap-2">
            <flux:button @click="deleteModal = false" variant="ghost" size="sm">Batal</flux:button>
            <flux:button
                @click="deletingIds.push(pendingDeleteId); deleteModal = false; $wire.deleteDailyTask(pendingDeleteId)"
                variant="danger" size="sm" wire:loading.attr="disabled" wire:loading.class="opacity-75"
                wire:target="deleteDailyTask">
                Hapus
            </flux:button>
        </div>
    </div>
</div>

@if ($reasonModalFor !== null)
<div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4">
    <div
        class="w-full rounded-t-2xl border border-zinc-200 bg-white p-6 shadow-xl sm:max-w-md sm:rounded-xl dark:border-zinc-700 dark:bg-zinc-900">
        <h3 class="mb-1 text-base font-semibold text-zinc-900 dark:text-zinc-100">Kenapa belum selesai?</h3>
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
            Jelaskan alasan task ini belum bisa diselesaikan hari ini.
        </p>

        <flux:textarea wire:model="reasonInputs.{{ $reasonModalFor }}" placeholder="Tulis alasanmu di sini..."
            rows="3" wire:keydown.ctrl.enter="submitReason" />

        @error("reasonInputs.{$reasonModalFor}")
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror

        <div class="mt-4 flex gap-2">
            <flux:button wire:click="$set('reasonModalFor', null)" variant="ghost" size="sm" class="flex-1"
                wire:loading.attr="disabled" wire:target="submitReason">
                Batal
            </flux:button>
            <flux:button wire:click="submitReason" variant="primary" size="sm" class="flex-1"
                wire:loading.attr="disabled" wire:loading.class="opacity-75" wire:target="submitReason">
                Simpan Alasan
            </flux:button>
        </div>
    </div>
</div>
@endif
