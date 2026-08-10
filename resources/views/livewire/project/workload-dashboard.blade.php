<div>
  @include('livewire.project.partials.workload.filters')

  {{-- ─── View: Per Daftar ───────────────────────────────── --}}
  @if ($view === 'task')
    @include('livewire.project.partials.workload.task-stat-cards')
    @include('livewire.project.partials.workload.task-groups')

    {{-- ─── View: Peringkat Tim (Leaderboard) ─────────────── --}}
  @elseif ($view === 'member')
    @include('livewire.project.partials.workload.member-stat-cards')
    @include('livewire.project.partials.workload.leaderboard')
  @endif
</div>
