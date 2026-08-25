<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <flux:heading size="xl">Tugas Saya</flux:heading>
    <flux:subheading>
      {{ $totalCount }} tugas ditugaskan kepada Anda di seluruh ruang
    </flux:subheading>
  </div>

  <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
    <div
      class="flex items-center rounded-lg border border-zinc-200 bg-zinc-50 p-0.5 dark:border-zinc-700 dark:bg-zinc-800">
      <button type="button" wire:click="switchView('list')" title="List"
        class="rounded-md px-2 py-1.5 text-xs font-medium transition-all sm:px-2.5
        {{ $view === 'list' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <flux:icon name="queue-list" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> List</span>
      </button>
      <button type="button" wire:click="switchView('calendar')" title="Calendar"
        class="rounded-md px-2 py-1.5 text-xs font-medium transition-all sm:px-2.5
        {{ $view === 'calendar' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <flux:icon name="calendar-days" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline">
          Calendar</span>
      </button>
    </div>

    <flux:select wire:model.live="filterPriority" size="sm" class="w-full sm:w-44">
      <flux:select.option value="">Semua Prioritas</flux:select.option>
      <flux:select.option value="urgent">🔴 Urgent</flux:select.option>
      <flux:select.option value="high">🟠 High</flux:select.option>
      <flux:select.option value="normal">🔵 Normal</flux:select.option>
      <flux:select.option value="low">⚪ Low</flux:select.option>
    </flux:select>
  </div>
</div>
