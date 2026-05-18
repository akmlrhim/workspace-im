@props(['name', 'keyPrefix', 'selectedTaskId'])

<flux:modal :name="$name" wire:model="showTaskDetail" :closable="false" @close="$wire.closeTaskDetail()"
  @task-deleted.window="if ($event.detail.taskId === $wire.selectedTaskId) $flux.modal('{{ $name }}').close()"
  class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
  <div
    class="relative min-h-[60vh] max-h-[85vh] overflow-y-auto pr-1 max-sm:max-h-none max-sm:min-h-0 max-sm:h-[calc(100dvh-4rem)]">
    <div wire:loading wire:target="openTaskDetail" class="absolute inset-0 z-50 bg-white dark:bg-zinc-900">
      @include('livewire.project.partials.task-detail-skeleton')
    </div>
    @if ($selectedTaskId)
      <livewire:project.task-detail :taskId="$selectedTaskId" :key="$keyPrefix . '-' . $selectedTaskId" />
    @else
      <div wire:loading.remove wire:target="openTaskDetail" class="flex min-h-[60vh] items-center justify-center">
        <x-spinner />
      </div>
    @endif
  </div>
</flux:modal>
