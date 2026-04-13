<?php

namespace App\Livewire\Project;

use App\Models\Project\TaskList;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Taskboard')]
class GeneralTaskboard extends Component
{
    public string $search = '';

    public function render()
    {
        $lists = TaskList::with(['space', 'folder', 'members'])
            ->withCount('tasks')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('space_id')
            ->orderBy('position')
            ->get();

        $grouped = $lists->groupBy(fn (TaskList $list) => $list->space->name ?? 'Tanpa Space');

        return view('livewire.project.general-taskboard', [
            'grouped' => $grouped,
            'totalLists' => $lists->count(),
        ]);
    }
}
