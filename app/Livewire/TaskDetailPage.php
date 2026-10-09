<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskDetailPage extends Component
{
    public Task $task;

    public function mount(Task $task): void
    {
        $this->ensureCanView($task);

        $this->task = $task;
    }

    public function getTitle(): string
    {
        return $this->task->title;
    }

    /**
     * The embedded detail panel asks to be closed (X button, deleted task,
     * lost access). On a standalone page that means navigating back to where
     * the task lives instead of hiding a modal.
     */
    #[On('close-task-detail')]
    public function close(): void
    {
        $this->redirect($this->backUrl(), navigate: true);
    }

    #[On('task-deleted')]
    public function onTaskDeleted(int $taskId): void
    {
        if ($this->task->id === $taskId) {
            $this->redirect($this->backUrl(), navigate: true);
        }
    }

    public function backUrl(): string
    {
        $taskList = $this->task->taskList()->with('space')->first();
        $space = $taskList?->space;

        if ($space && $taskList) {
            return route('lists.board', [$space, $taskList]);
        }

        return route('my-tasks');
    }

    private function ensureCanView(Task $task): void
    {
        $user = auth()->user();
        $taskList = $task->taskList;

        abort_unless($user !== null && $taskList !== null, 403);

        if ($taskList->isAccessibleBy($user)) {
            return;
        }

        abort_unless($task->canBeManagedBy($user), 403);
    }

    public function render()
    {
        $taskList = $this->task->taskList()->with('space')->first();

        return view('livewire.task-detail-page', [
            'space' => $taskList?->space,
            'taskList' => $taskList,
        ]);
    }
}
