<div>
  <flux:modal wire:model="showDeleteConfirm" class="w-full max-w-sm">
    <div class="space-y-4 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
        <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
      </div>
      <flux:heading size="lg">Delete Task?</flux:heading>
      <p class="text-sm text-zinc-500 dark:text-zinc-400">This task and all its subtasks will be permanently deleted. This action cannot be undone.</p>
      <div class="flex justify-center gap-2 pt-2">
        <flux:button variant="ghost" @click="$wire.set('showDeleteConfirm', false)">Cancel</flux:button>
        <flux:button variant="danger" wire:click="deleteTask">Delete</flux:button>
      </div>
    </div>
  </flux:modal>
</div>
