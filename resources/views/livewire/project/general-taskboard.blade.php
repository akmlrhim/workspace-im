<div x-data="generalTaskboard">
  @include('livewire.project.partials.taskboard.header')

  @if ($activeTab === 'lists')
    @include('livewire.project.partials.taskboard.spaces-grid')
  @endif

  @if ($activeTab === 'calendar')
    @include('livewire.project.partials.taskboard.calendar')
  @endif

  {{-- ─── Modals ──────────────────────────────────────────── --}}
  @include('livewire.project.partials.taskboard.modals')

  {{-- Task Detail Modal (from calendar) --}}
  <x-task-detail-modal name="gen-task-detail" keyPrefix="gen-detail" :selectedTaskId="$selectedTaskId" />
</div>

@script
<script>
    Alpine.data('generalTaskboard', () => ({
        deletingSpaceId: null,
        deletingSpaceName: '',
        deletedSpaceIds: [],
        deletingListId: null,
        deletingListName: '',
        deletedListIds: [],
        membersLoading: false,
        collapsedSpaces: [],
        toggleSpace(id) {
            if (this.collapsedSpaces.includes(id)) {
                this.collapsedSpaces = this.collapsedSpaces.filter(i => i !== id);
            } else {
                this.collapsedSpaces.push(id);
            }
        },
    }));
</script>
@endscript
