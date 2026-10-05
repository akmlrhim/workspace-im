<flux:dropdown position="bottom" align="end" teleport>
    <div class="relative">
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 z-10 flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
            </span>
        @endif
        <flux:button variant="ghost" icon="bell" aria-label="{{ __('Notifikasi') }}"></flux:button>
    </div>

    <flux:menu class="w-[calc(100vw-2rem)] max-w-80 max-h-96 overflow-y-auto sm:w-80">
        <div class="flex items-center justify-between px-3 py-2 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center gap-2">
                <flux:heading size="sm">{{ __('Notifikasi') }}</flux:heading>
                @if ($unreadCount > 0)
                    <flux:badge size="sm" color="rose">{{ $unreadCount }}</flux:badge>
                @endif
            </div>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead" type="button" class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 cursor-pointer">
                    {{ __('Tandai dibaca') }}
                </button>
            @endif
        </div>

        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
            @endphp
            <div
                wire:click="markAsRead('{{ $notification->id }}')"
                class="p-3 border-b border-zinc-100 dark:border-zinc-800/50 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition cursor-pointer {{ $isUnread ? 'bg-zinc-50/80 dark:bg-zinc-800/30' : '' }}"
            >
                <div class="flex items-start gap-2">
                    @if ($isUnread)
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-zinc-900 dark:text-zinc-100 truncate">
                            {{ $data['title'] ?? __('Notifikasi Baru') }}
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">
                            {{ $data['message'] ?? ($data['body'] ?? '') }}
                        </p>
                        <span class="text-[10px] text-zinc-400 mt-1 block">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-zinc-500">
                {{ __('Tidak ada notifikasi') }}
            </div>
        @endforelse
    </flux:menu>
</flux:dropdown>
