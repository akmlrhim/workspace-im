<div
  x-data
  @task-form-modal-open.window="$flux.modal('{{ $taskFormModalName }}').show()"
  @task-form-modal-close.window="$flux.modal('{{ $taskFormModalName }}').close()">
  <flux:modal name="{{ $taskFormModalName }}" @close="$wire.closeForm()"
    class="w-full max-w-lg max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
    <div class="space-y-6 max-sm:overflow-y-auto max-sm:pb-8">
      <flux:heading size="lg">{{ $editingTaskId ? 'Edit Task' : 'Create Task' }}</flux:heading>

      <form wire:submit="saveTask" class="space-y-4">
        <flux:field>
          <flux:label>Judul tugas</flux:label>
          <flux:input wire:model="formTaskTitle" placeholder="Apa yang dikerjakan?" autofocus />
          <flux:error name="formTaskTitle" />
        </flux:field>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
          @if ($workspaceUsers->isEmpty())
            <div
              class="flex items-center gap-2 rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-xs text-zinc-400 dark:border-zinc-700">
              <flux:icon name="users" class="size-4 shrink-0" />
              <span>Belum ada anggota di list ini. Tambahkan anggota terlebih dahulu melalui halaman Space.</span>
            </div>
          @else
            <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
              @foreach ($workspaceUsers as $member)
                <label
                  class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                  <flux:checkbox wire:model="formTaskAssignees" :value="$member->id" />
                  <flux:avatar circle :name="$member->name" :initials="$member->initials()" :src="$member->avatar"
                    size="xs" />
                  <span class="text-zinc-700 dark:text-zinc-300">{{ $member->name }}</span>
                </label>
              @endforeach
            </div>
          @endif
        </flux:field>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" type="button"
            @click="$flux.modal('{{ $taskFormModalName }}').close()">Cancel</flux:button>
          <flux:button type="submit" variant="primary">Save</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>
</div>
