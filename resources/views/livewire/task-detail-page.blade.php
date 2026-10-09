<div class="mx-auto w-full max-w-5xl px-1 py-4">
  @if ($space && $taskList)
    @include('livewire.partials.breadcrumb')
  @endif

  <div
    class="mt-2 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-6 dark:border-zinc-700/60 dark:bg-zinc-900">
    <livewire:task-detail :taskId="$task->id" :standalone="true" :key="'page-'.$task->id" />
  </div>

  <livewire:task-delete-modal />
</div>
