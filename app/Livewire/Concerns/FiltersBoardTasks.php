<?php

namespace App\Livewire\Concerns;

use App\Models\Task;
use App\Models\TaskLabel;
use Livewire\Attributes\Computed;

trait FiltersBoardTasks
{
    public ?int $filterAssigneeId = null;

    public ?string $filterPriority = null;

    public ?int $filterLabelId = null;

    public string $search = '';

    #[Computed]
    public function hasActiveFilter(): bool
    {
        return filled($this->filterAssigneeId)
            || filled($this->filterPriority)
            || filled($this->filterLabelId)
            || filled(trim($this->search));
    }

    #[Computed]
    public function searchSuggestions()
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Task::query()
            ->whereNull('parent_id')
            ->where('task_list_id', $this->taskList->id)
            ->where('title', 'like', '%'.addcslashes($term, '\\%_').'%')
            ->with('status:id,name,color')
            ->orderBy('title')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function availableAssignees()
    {
        return $this->taskList->members()->orderBy('name')->get();
    }

    #[Computed]
    public function availableLabels()
    {
        $this->taskList->loadMissing('space.workspace');

        return TaskLabel::where('workspace_id', $this->taskList->space->workspace_id)
            ->orderBy('name')
            ->get();
    }

    public function clearFilters(): void
    {
        $this->filterAssigneeId = null;
        $this->filterPriority = null;
        $this->filterLabelId = null;
        $this->search = '';
        unset($this->statuses);
    }

    public function updatedSearch(): void
    {
        unset($this->statuses);
    }

    public function updatedFilterAssigneeId(): void
    {
        unset($this->statuses);
    }

    public function updatedFilterPriority(): void
    {
        unset($this->statuses);
    }

    public function updatedFilterLabelId(): void
    {
        unset($this->statuses);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Relations\Relation<Task, *, *>  $query
     */
    private function applyTaskFilters($query): void
    {
        $search = trim($this->search);

        if ($this->filterAssigneeId) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $this->filterAssigneeId));
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterLabelId) {
            $query->whereHas('labels', fn ($q) => $q->where('task_labels.id', $this->filterLabelId));
        }

        if ($search !== '') {
            $query->where('title', 'like', '%'.addcslashes($search, '\\%_').'%');
        }
    }
}
