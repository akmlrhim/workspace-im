<div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <flux:heading size="xl">Workload & Traffic</flux:heading>
  <flux:input onfocus="this.showPicker()" onclick="this.showPicker()" type="month" wire:model.live="selectedMonth" size="sm" icon="calendar"
    class="w-full sm:w-56" />
</div>

@if ($spaces->isNotEmpty())
  <div class="mb-6 flex flex-wrap gap-2">
    <button wire:click="selectSpace(null)"
      class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition
        {{ $selectedSpaceId === null
            ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500/60 dark:bg-indigo-500/10 dark:text-indigo-300'
            : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-200' }}">
      <flux:icon name="squares-2x2" class="size-3" />
      Semua Space
    </button>

    @foreach ($spaces as $space)
      <button wire:click="selectSpace({{ $space->id }})" wire:key="space-pill-{{ $space->id }}"
        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition
          {{ $selectedSpaceId === $space->id
              ? 'border-current bg-opacity-10 font-semibold'
              : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-200' }}"
        @if ($selectedSpaceId === $space->id) style="border-color: {{ $space->color }}; color: {{ $space->color }}; background-color: {{ $space->color }}15;" @endif>
        <div class="size-2 rounded-full shrink-0" style="background-color: {{ $space->color }}"></div>
        {{ $space->name }}
      </button>
    @endforeach
  </div>
@endif

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <div
    class="flex w-full sm:inline-flex sm:w-auto items-center rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
    <button wire:click="switchView('task')"
      class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all
        {{ $view === 'task' ? 'bg-white text-blue-700 shadow-sm dark:bg-zinc-700 dark:text-blue-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <span class="flex items-center justify-center gap-2">
        <flux:icon name="clipboard-document-list" variant="micro" class="size-4 shrink-0" />
        <span class="truncate text-sm">View By Task</span>
      </span>
    </button>
    <button wire:click="switchView('member')"
      class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all
        {{ $view === 'member' ? 'bg-white text-blue-700 shadow-sm dark:bg-zinc-700 dark:text-blue-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <span class="flex items-center justify-center gap-2">
        <flux:icon name="trophy" variant="micro" class="size-4 shrink-0" />
        <span class="truncate text-sm">Peringkat</span>
      </span>
    </button>
  </div>

  @if ($view === 'task')
    @php $activeMember = $selectedMemberId ? $this->members->firstWhere('id', $selectedMemberId) : null; @endphp

    <div class="flex items-center gap-2">
      @if ($activeMember)
        <flux:avatar circle size="xs" :name="$activeMember->name" :initials="$activeMember->initials()"
          :src="$activeMember->avatar" class="shrink-0" />
      @endif

      <flux:select wire:model.live="selectedMemberId" size="sm" class="min-w-0 flex-1 sm:w-52 sm:flex-none">
        <flux:select.option value="">Semua Anggota</flux:select.option>
        @foreach ($this->members as $member)
          <flux:select.option value="{{ $member->id }}">{{ $member->name }}</flux:select.option>
        @endforeach
      </flux:select>

      @if ($activeMember)
        <button wire:click="$set('selectedMemberId', null)" title="Tampilkan semua anggota"
          class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
          <flux:icon name="x-mark" class="size-4" />
        </button>
      @endif
    </div>
  @endif
</div>
