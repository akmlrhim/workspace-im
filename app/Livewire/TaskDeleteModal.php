<?php

namespace App\Livewire;

use App\Events\TaskUpdated as TaskUpdatedEvent;
use App\Models\Task;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskDeleteModal extends Component
{
	public bool $showDeleteConfirm = false;

	public ?int $deletingTaskId = null;

	#[On('open-delete-task-modal')]
	public function confirmDelete(int $taskId): void
	{
		$task = Task::findOrFail($taskId);

		if (! $task->canBeManagedBy(auth()->user())) {
			Flux::toast('Anda tidak memiliki izin untuk menghapus tugas ini.', variant: 'danger');

			return;
		}

		$this->deletingTaskId = $taskId;
		$this->showDeleteConfirm = true;
	}

	public function deleteTask(): void
	{
		if ($this->deletingTaskId) {
			$task = Task::with('taskList.space')->findOrFail($this->deletingTaskId);

			if (! $task->canBeManagedBy(auth()->user())) {
				Flux::toast('Anda tidak memiliki izin untuk menghapus tugas ini.', variant: 'danger');
				$this->reset(['deletingTaskId', 'showDeleteConfirm']);

				return;
			}

			$taskId = $task->id;
			$taskListId = $task->task_list_id;
			$workspaceId = $task->taskList?->space?->workspace_id;

			$task->delete();
			Flux::toast('Tugas berhasil dihapus.', variant: 'danger');
			$this->dispatch('task-deleted', taskId: $taskId);
			$this->dispatch('task-updated');

			TaskUpdatedEvent::dispatch($taskId, auth()->id(), $taskListId, $workspaceId);
		}
		$this->reset(['deletingTaskId', 'showDeleteConfirm']);
	}

	public function render()
	{
		return view('livewire.task-delete-modal');
	}
}
