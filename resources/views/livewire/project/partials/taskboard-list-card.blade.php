<div x-data="{ listName: @js($list->name), memberIds: @js($list->members->pluck('id')->values()->all()) }"
  class="group relative flex flex-col rounded-xl border border-zinc-200 bg-white p-4 transition-all duration-200 hover:border-indigo-300 hover:shadow-md dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:hover:border-indigo-500/50 dark:hover:bg-zinc-800">

  <div class="flex items-start justify-between gap-4">
    <div class="flex min-w-0 flex-1 items-center gap-3">
      <div class="min-w-0 flex-1">
        <a href="{{ route('project-management.lists.board', [$list->space, $list]) }}" wire:navigate
          class="block truncate text-base font-semibold text-zinc-900 transition-colors duration-200 group-hover:text-indigo-600 focus:outline-none dark:text-zinc-100 dark:group-hover:text-indigo-400 before:absolute before:inset-0 before:z-10 before:rounded-xl focus-visible:before:ring-2 focus-visible:before:ring-indigo-500 focus-visible:before:ring-offset-2 dark:focus-visible:before:ring-offset-zinc-900"
          aria-label="Buka board {{ $list->name }}">
          {{ $list->name }}
        </a>
        <div class="mt-1 flex items-center gap-2">
          <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
            <flux:icon name="document-text" class="size-3.5" />
            {{ $list->tasks_count }} tasks
          </span>
        </div>
      </div>
    </div>

    @if (auth()->user()->canManageLists())
      <div class="relative z-20 flex shrink-0 items-center gap-1">
        <button type="button"
          @click.prevent="$wire.managingListId = {{ $list->id }}; $wire.listMemberIds = memberIds; $wire.showManageMembers = true; $flux.modal('manage-members-modal').show()"
          class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-indigo-600 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:hover:bg-zinc-700 dark:hover:text-indigo-400"
          title="Kelola Anggota List">
          <flux:icon name="users" class="size-4" />
        </button>
        <button type="button"
          @click.prevent="$wire.editingListId = {{ $list->id }}; $wire.editListName = listName; $wire.editListSpaceId = {{ $list->space_id }}; $wire.showEditList = true; $flux.modal('edit-list-modal').show()"
          class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-blue-600 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-zinc-700 dark:hover:text-blue-400"
          title="Edit list">
          <flux:icon name="pencil-square" class="size-4" />
        </button>
      </div>
    @endif
  </div>

  <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-700/50">
    <div class="relative z-20">
      @if ($list->members->isNotEmpty())
        <flux:avatar.group>
          @foreach ($list->members->take(4) as $member)
            <flux:avatar circle :name="$member->name" :initials="$member->initials()" :src="$member->avatar"
              size="xs" class="ring-2 ring-white hover:z-30 dark:ring-zinc-800" />
          @endforeach
          @if ($list->members->count() > 4)
            <flux:avatar circle size="xs" class="ring-2 ring-white dark:ring-zinc-800">
              +{{ $list->members->count() - 4 }}
            </flux:avatar>
          @endif
        </flux:avatar.group>
      @else
        <span class="text-xs italic text-zinc-400 dark:text-zinc-500">Belum ada anggota</span>
      @endif
    </div>

    <div class="flex items-center gap-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400">
      <span>Buka board</span>
      <flux:icon name="chevron-right" class="size-3.5" />
    </div>
  </div>
</div>
