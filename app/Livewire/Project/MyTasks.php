<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('My Tasks')]
class MyTasks extends Component
{
    public string $filterPriority = '';
    public string $filterStatus = '';

    public ?int $selectedTaskId = null;
    public bool $showTaskDetail = false;

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
        $this->selectedTaskId = null;
    }

    public function render()
    {
        $userId = auth()->id();

        $query = Task::where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->orWhereHas('assignees', fn ($sub) => $sub->where('user_id', $userId));
            })
            ->with(['status', 'taskList.space', 'labels', 'assignees'])
            ->whereNull('parent_id');

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        $tasks = $query->orderByDesc('updated_at')->get();

        return view('livewire.project.my-tasks', [
            'tasks' => $tasks,
        ]);
    }
}
