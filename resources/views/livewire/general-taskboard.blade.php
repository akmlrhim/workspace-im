<div x-data="generalTaskboard">
  @include('livewire.partials.taskboard.header')

  @if ($activeTab === 'lists')
    @include('livewire.partials.taskboard.spaces-grid')
  @endif

  @if ($activeTab === 'calendar')
    @include('livewire.partials.taskboard.calendar')
  @endif

  @include('livewire.partials.taskboard.modals')

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
