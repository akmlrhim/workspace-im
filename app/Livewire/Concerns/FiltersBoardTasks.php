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

    /**
     * Escape a user search term so `%`, `_` and `\` are matched literally.
     *
     * MySQL treats `\` as the default LIKE escape character, but SQLite needs
     * the escape character declared explicitly, so the query always spells out
     * `ESCAPE '\'` to behave the same on every driver.
     */
    private function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }

    /**
     * Apply a literal substring match on a column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Task>|\Illuminate\Database\Eloquent\Relations\Relation<Task, *, *>  $query
     */
    private function whereTitleContains($query, string $column, string $term): void
    {
        $query->whereRaw("{$column} like ? escape '\\'", ['%'.$this->escapeLike($term).'%']);
    }

    #[Computed]
    public function searchSuggestions()
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        $query = Task::query()
            ->whereNull('parent_id')
            ->where('task_list_id', $this->taskList->id)
            ->with('status:id,name,color')
            ->orderBy('title')
            ->limit(8);

        $this->whereTitleContains($query, 'title', $term);

        return $query->get();
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
            $this->whereTitleContains($query, 'title', $search);
        }
    }
}
