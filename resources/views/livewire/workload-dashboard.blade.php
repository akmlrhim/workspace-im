<div>
  @include('livewire.partials.workload.filters')

  @if ($view === 'task')
    @include('livewire.partials.workload.task-stat-cards')
    @include('livewire.partials.workload.task-groups')

  @elseif ($view === 'member')
    @include('livewire.partials.workload.member-stat-cards')
    @include('livewire.partials.workload.leaderboard')
  @endif
</div>
