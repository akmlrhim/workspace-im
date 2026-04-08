<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use Livewire\Component;
use Livewire\Attributes\On;
use Flux\Flux;

class TaskDeleteModal extends Component
{
	public bool $showDeleteConfirm = false;
	public ?int $deletingTaskId = null;

	#[On('open-delete-task-modal')]
	public function confirmDelete(int $taskId): void
	{
		$task = Task::findOrFail($taskId);

		if (!$task->canBeManagedBy(auth()->user())) {
			Flux::toast(__('messages.no_permission_delete_task'), variant: 'danger');
			return;
		}

		$this->deletingTaskId = $taskId;
		$this->showDeleteConfirm = true;
	}

	public function deleteTask(): void
	{
		if ($this->deletingTaskId) {
			Task::findOrFail($this->deletingTaskId)->delete();
			Flux::toast(__('messages.task_deleted'), variant: 'danger');
			$this->dispatch('task-updated'); // Tell parent views to refresh
		}
		$this->reset(['deletingTaskId', 'showDeleteConfirm']);
	}

	public function render()
	{
		return view('livewire.project.task-delete-modal');
	}
}
