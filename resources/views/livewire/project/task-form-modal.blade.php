<div>
  <flux:modal wire:model="showTaskForm" class="w-full max-w-lg">
    <div class="space-y-6">
      <flux:heading size="lg">{{ $editingTaskId ? 'Edit Task' : 'Create Task' }}</flux:heading>

      <form wire:submit="saveTask" class="space-y-4">
        <flux:field>
          <flux:label>Title</flux:label>
          <flux:input wire:model="formTaskTitle" placeholder="What needs to be done?" autofocus />
          <flux:error name="formTaskTitle" />
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
          <flux:field>
            <flux:label>Status</flux:label>
            <flux:select wire:model="formTaskStatusId">
              @foreach ($statuses as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
              @endforeach
            </flux:select>
          </flux:field>

          <flux:field>
            <flux:label>Priority</flux:label>
            <flux:select wire:model="formTaskPriority">
              <option value="urgent">🔴 Urgent</option>
              <option value="high">🟠 High</option>
              <option value="normal">🔵 Normal</option>
              <option value="low">⚪ Low</option>
            </flux:select>
          </flux:field>
        </div>
        <flux:field>
          <flux:label>Assignees</flux:label>
          <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
            @foreach ($workspaceUsers as $member)
              <label
                class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                <input type="checkbox" wire:model="formTaskAssignees" value="{{ $member->id }}"
                  class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800" />
                <flux:avatar :name="$member->name" :initials="$member->initials()" size="xs" />
                <span class="text-zinc-700 dark:text-zinc-300">{{ $member->name }}</span>
              </label>
            @endforeach
          </div>
        </flux:field>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" wire:click="$set('showTaskForm', false)">Cancel</flux:button>
          <flux:button type="submit" variant="primary">Save</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>
</div>
