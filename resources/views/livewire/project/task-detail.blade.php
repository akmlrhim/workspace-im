@php $ro = !$canManage; @endphp
<div x-data="{ activeTab: @entangle('activeTab') }">
  @if ($task)
    @php
      $checklistTotal = $checklists->sum(fn($c) => $c->items->count());
      $checklistDone = $checklists->sum(fn($c) => $c->items->where('is_completed', true)->count());
      $commentCount = $comments->count();
    @endphp

    @include('livewire.project.partials.task-detail.header')

    {{-- Tab Content --}}
    <div class="pt-6">
      @include('livewire.project.partials.task-detail.tab-overview')
      @include('livewire.project.partials.task-detail.tab-checklist')
      @include('livewire.project.partials.task-detail.tab-comments')
      @include('livewire.project.partials.task-detail.tab-activity')
    </div>

    <div class="mt-6 border-t border-zinc-100 pt-3 text-xs text-zinc-400 dark:border-zinc-700/50 dark:text-zinc-500">
      Dibuat oleh {{ $task->creator?->name ?? 'Unknown' }} ·
      {{ $task->created_at->format('d M Y \\p\\u\\k\\u\\l H:i') }}
    </div>
  @else
    <div class="flex items-center justify-center py-12">
      <p class="text-zinc-500">Tugas tidak ditemukan</p>
    </div>
  @endif
</div>
