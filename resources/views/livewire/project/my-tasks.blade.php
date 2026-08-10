<div class="space-y-6">
  {{-- Header --}}
  @include('livewire.project.partials.my-tasks.header')

  @if ($view === 'calendar')
    @include('livewire.project.partials.my-tasks.calendar')
  @elseif ($totalCount === 0)
    <x-empty-state
      icon="check-circle"
      iconBgClass="bg-green-100 dark:bg-green-500/20"
      iconClass="size-9 text-green-600 dark:text-green-400"
      heading="Tidak ada tugas"
      subheading="Anda tidak memiliki tugas yang ditugaskan saat ini." />
  @else
    @include('livewire.project.partials.my-tasks.grouped-list')
  @endif

  <x-task-detail-modal name="task-detail-mytasks" keyPrefix="my-detail" :selectedTaskId="$selectedTaskId" />
</div>
