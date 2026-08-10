<div>
  {{-- Header --}}
  <div class="mb-6">
    @include('livewire.project.partials.breadcrumb')

    <div class="mt-3 flex items-center justify-between gap-3 mb-4">
      <h1 class="hidden lg:block lg:text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>
    </div>

    @include('livewire.project.partials.view-toggle', ['active' => 'notes'])
  </div>

  {{-- Toolbar --}}
  <div class="mb-4 flex items-center justify-between gap-3">

    @if ($canManage)
      <flux:button variant="primary" size="sm" icon="plus" wire:click="$set('showNewNoteForm', true)">
        Tambah Catatan
      </flux:button>
    @endif
  </div>

  {{-- Notes list --}}
  @if ($this->notes->isEmpty())
    <div
      class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 py-16 dark:border-zinc-700">
      <flux:icon name="document-text" class="size-10 text-zinc-300 dark:text-zinc-600" />
      <p class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Belum ada catatan</p>
      @if ($canManage)
        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Tambah catatan untuk menyimpan informasi, link, dan
          file.</p>
      @endif
    </div>
  @else
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      @foreach ($this->notes as $note)
        @include('livewire.project.partials.notes.note-card')
      @endforeach
    </div>
  @endif

  {{-- ─── Modals ─────────────────────────────────────────────── --}}

  @if ($canManage)
    @include('livewire.project.partials.notes.create-modal')
    @include('livewire.project.partials.notes.edit-modals')
  @endif
</div>
