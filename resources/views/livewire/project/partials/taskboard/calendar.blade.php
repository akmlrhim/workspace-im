<div class="mb-4 flex items-center justify-between gap-4">
  <div class="flex shrink-0 items-center gap-1">
    <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="calPrevMonth" />
    <flux:button size="sm" variant="ghost" wire:click="calToday">Hari Ini</flux:button>
    <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="calNextMonth" />
  </div>

  <h2 class="truncate text-base font-semibold text-zinc-900 dark:text-white sm:text-lg">{{ $calMonthLabel }}</h2>

  @if ($this->spaces->isNotEmpty())
    <div class="flex shrink-0 items-center gap-1.5">
      <flux:select wire:model.live="calSelectedSpaceId" size="sm" class="w-36 sm:w-44">
        @foreach ($this->spaces as $sp)
          <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
        @endforeach
      </flux:select>
    </div>
  @endif
</div>

@include('livewire.project.partials.taskboard.calendar-month')

{{-- Mobile: list-style calendar --}}
@include('livewire.project.partials.taskboard.calendar-agenda')
