<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
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
			Task::findOrFail($this->deletingTaskId)->delete();
			Flux::toast('Tugas berhasil dihapus.', variant: 'danger');
			$this->dispatch('task-updated'); // Tell parent views to refresh
		}
		$this->reset(['deletingTaskId', 'showDeleteConfirm']);
	}

	public function render()
	{
		return view('livewire.project.task-delete-modal');
	}
}
