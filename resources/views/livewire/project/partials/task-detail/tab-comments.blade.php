{{-- Comments tab: new comment form, comment list, replies --}}
<div x-show="activeTab === 'comments'" x-cloak>
  <form wire:submit="addComment" class="mb-4">
    <flux:textarea wire:model="newComment" placeholder="Tambahkan komentar... (Ctrl+Enter untuk kirim)" rows="3"
      x-on:keydown.ctrl.enter.prevent="$wire.addComment()" x-on:keydown.meta.enter.prevent="$wire.addComment()" />
    <div class="mt-2 flex items-center justify-between">
      <span class="text-[10px] text-zinc-400 dark:text-zinc-500">Tekan <kbd
          class="rounded bg-zinc-100 px-1 py-0.5 text-[9px] font-mono dark:bg-zinc-800">Ctrl+Enter</kbd> untuk
        kirim</span>
      <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane">Kirim</flux:button>
    </div>
  </form>

  @forelse ($comments as $comment)
    <div class="mb-3 rounded-lg border border-zinc-100 p-3 dark:border-zinc-700" wire:key="comment-{{ $comment->id }}">
      <div class="mb-2 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <flux:avatar circle :name="$comment->user?->name ?? 'Unknown'" :initials="$comment->user?->initials() ?? '?'"
            :src="$comment->user?->avatar" size="xs" />
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
            <flux:button icon="trash" size="xs" variant="ghost" wire:click="deleteComment({{ $comment->id }})"
              class="text-red-500" />
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
            x-on:keydown.ctrl.enter.prevent="$wire.addReply()" x-on:keydown.meta.enter.prevent="$wire.addReply()"
            x-on:keydown.escape="$wire.cancelReply()" autofocus />
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
