@php
  $isDone = $task['status_type'] === 'closed';
  $priorityLabel = match ($task['priority']) {
      'urgent' => 'Mendesak',
      'high' => 'Tinggi',
      'normal' => 'Normal',
      'low' => 'Rendah',
      default => 'Normal',
  };
  $priorityColor = match ($task['priority']) {
      'urgent' => 'red',
      'high' => 'orange',
      'normal' => 'blue',
      'low' => 'zinc',
      default => 'blue',
  };
@endphp

<tr
  class="border-b transition last:border-b-0
    {{ $isDone
        ? 'border-emerald-50 bg-emerald-50/50 hover:bg-emerald-50 dark:border-emerald-900/20 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/15'
        : 'border-zinc-50 hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50' }}"
  wire:key="task-{{ $task['id'] }}">

  <td class="px-6 py-4">
    <div class="flex items-center gap-2">
      @if ($isDone)
        <svg class="size-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
          stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span class="font-medium text-emerald-700 dark:text-emerald-400">
          {{ $task['title'] }}
        </span>
      @else
        <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $task['title'] }}</span>
      @endif
    </div>
  </td>

  <td class="px-6 py-4">
    <flux:badge color="{{ $isDone ? 'zinc' : $priorityColor }}" size="sm" class="{{ $isDone ? 'opacity-60' : '' }}">
      {{ $priorityLabel }}
    </flux:badge>
  </td>

  <td class="px-6 py-4">
    @if ($isDone)
      <span
        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        Selesai
      </span>
    @else
      @php
        $statusColor = match ($task['status_type']) {
            'active' => 'teal',
            default => 'zinc',
        };
        $statusLabel = match ($task['status_type']) {
            'active' => 'Sedang Dikerjakan',
            default => 'Belum Dimulai',
        };
      @endphp
      <flux:badge color="{{ $statusColor }}" size="sm">{{ $statusLabel }}</flux:badge>
    @endif
  </td>

  <td class="px-6 py-4">
    @if ($task['assignees']->isNotEmpty())
      <div class="flex items-center gap-1">
        @foreach ($task['assignees']->take(3) as $assignee)
          <flux:avatar circle size="xs" :name="$assignee->name" :initials="$assignee->initials()"
            :src="$assignee->avatar" class="{{ $isDone ? 'opacity-70' : '' }}" />
        @endforeach
        @if ($task['assignees']->count() > 3)
          <span class="text-xs text-zinc-400">+{{ $task['assignees']->count() - 3 }}</span>
        @endif
      </div>
    @else
      <span class="text-zinc-400">Belum ditugaskan</span>
    @endif
  </td>

  <td class="px-6 py-4">
    @if ($task['due_date'])
      @if ($isDone)
        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
          <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm">{{ $task['due_date']->format('d M Y') }}</span>
        </span>
      @elseif ($task['is_overdue'])
        <span class="font-medium text-red-600 dark:text-red-400">
          {{ $task['due_date']->format('d M Y') }}
        </span>
        <div class="mt-0.5 text-xs text-red-500">Melewati Tenggat</div>
      @else
        <span class="text-zinc-600 dark:text-zinc-400">{{ $task['due_date']->format('d M Y') }}</span>
      @endif
    @else
      <span class="text-zinc-400">–</span>
    @endif
  </td>

  <td class="px-6 py-4 {{ $isDone ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-400' }}">
    {{ $task['hours'] > 0 ? $task['hours'] . ' jam' : '–' }}
  </td>
</tr>
