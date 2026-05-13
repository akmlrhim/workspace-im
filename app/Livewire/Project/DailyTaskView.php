<?php

namespace App\Livewire\Project;

use App\Models\Project\DailyTask;
use App\Models\Project\DailyTaskLog;
use App\Models\Project\Space;
use App\Models\Project\TaskList;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DailyTaskView extends Component
{
	public Space $space;

	public TaskList $taskList;

	public string $selectedDate = '';

	public array $reasonInputs = [];

	public ?int $reasonModalFor = null;

	public function mount(Space $space, TaskList $taskList): void
	{
		$this->space = $space;
		$this->taskList = $taskList;
		$this->selectedDate = today()->toDateString();
	}

	#[Computed]
	public function isToday(): bool
	{
		return $this->selectedDate === today()->toDateString();
	}

	#[Computed]
	public function selectedCarbon(): Carbon
	{
		return Carbon::parse($this->selectedDate);
	}

	#[Computed]
	public function selectedDayOfWeek(): int
	{
		return Carbon::parse($this->selectedDate)->isoWeekday();
	}

	#[Computed]
	public function dailyTasks(): Collection
	{
		return $this->taskList->dailyTasks()
			->where('is_active', true)
			->where('day_of_week', $this->selectedDayOfWeek)
			->with([
				'creator',
				'logs' => fn($q) => $q->where('user_id', auth()->id())->where('date', $this->selectedDate),
			])
			->orderBy('position')
			->get();
	}

	#[Computed]
	public function pendingCount(): int
	{
		return $this->dailyTasks->filter(function (DailyTask $dt) {
			$log = $dt->logs->first();

			return ! ($log && $log->is_completed);
		})->count();
	}

	#[Computed]
	public function completedCount(): int
	{
		return $this->dailyTasks->count() - $this->pendingCount;
	}

	public function updatedSelectedDate(): void
	{
		// Clamp future dates to today
		if ($this->selectedDate > today()->toDateString()) {
			$this->selectedDate = today()->toDateString();
		}

		unset($this->dailyTasks, $this->pendingCount, $this->completedCount, $this->isToday, $this->selectedCarbon, $this->selectedDayOfWeek);
	}

	public function previousDay(): void
	{
		$this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
		unset($this->dailyTasks, $this->pendingCount, $this->completedCount, $this->isToday, $this->selectedCarbon, $this->selectedDayOfWeek);
	}

	public function nextDay(): void
	{
		if ($this->isToday) {
			return;
		}

		$this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
		unset($this->dailyTasks, $this->pendingCount, $this->completedCount, $this->isToday, $this->selectedCarbon, $this->selectedDayOfWeek);
	}

	public function goToToday(): void
	{
		$this->selectedDate = today()->toDateString();
		unset($this->dailyTasks, $this->pendingCount, $this->completedCount, $this->isToday, $this->selectedCarbon, $this->selectedDayOfWeek);
	}

	private function canManage(): bool
	{
		$user = auth()->user();

		if ($user->canManageAllProjects()) {
			return true;
		}

		if ($user->isManager() && $this->taskList->isAccessibleBy($user)) {
			return true;
		}

		if ($this->taskList->members()->where('users.id', $user->id)->exists()) {
			return true;
		}

		return $this->taskList->tasks()->whereHas('assignees', fn($q) => $q->where('users.id', $user->id))->exists()
			|| $this->taskList->tasks()->where('assigned_to', $user->id)->exists();
	}

	public function addDailyTask(string $title, string $description = ''): void
	{
		if (! $this->canManage()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

			return;
		}

		$title = trim($title);
		$description = trim($description);

		abort_if($title === '' || strlen($title) > 255, 422);

		$dayOfWeek = $this->selectedDayOfWeek;
		$maxPosition = $this->taskList->dailyTasks()->where('day_of_week', $dayOfWeek)->max('position') ?? -1;

		$this->taskList->dailyTasks()->create([
			'title' => $title,
			'description' => $description ?: null,
			'created_by' => auth()->id(),
			'position' => $maxPosition + 1,
			'day_of_week' => $dayOfWeek,
		]);

		unset($this->dailyTasks, $this->pendingCount, $this->completedCount);

		Flux::toast('Daily task ditambahkan.', variant: 'success');
	}

	public function saveEdit(int $id, string $title, string $description = ''): void
	{
		if (! $this->canManage()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

			return;
		}

		$title = trim($title);
		$description = trim($description);

		abort_if($title === '' || strlen($title) > 255, 422);

		$dailyTask = $this->taskList->dailyTasks()->findOrFail($id);

		abort_if($dailyTask->created_by !== auth()->id(), 403);

		$dailyTask->update([
			'title' => $title,
			'description' => $description ?: null,
		]);

		unset($this->dailyTasks);

		Flux::toast('Daily task diperbarui.', variant: 'success');
	}

	public function toggleComplete(int $dailyTaskId): void
	{
		if (! $this->canManage()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

			return;
		}

		$log = DailyTaskLog::firstOrNew([
			'daily_task_id' => $dailyTaskId,
			'user_id' => auth()->id(),
			'date' => $this->selectedDate,
		]);

		if ($log->is_completed) {
			$log->is_completed = false;
			$log->completed_at = null;
			$log->reason = null;
			$log->save();
		} else {
			$log->is_completed = true;
			$log->completed_at = now();
			$log->reason = null;
			$log->save();
		}

		unset($this->dailyTasks, $this->pendingCount, $this->completedCount);
	}

	public function openReasonModal(int $dailyTaskId): void
	{
		if (! $this->canManage()) {
			return;
		}

		$this->reasonModalFor = $dailyTaskId;
		$this->reasonInputs[$dailyTaskId] = '';
	}

	public function submitReason(): void
	{
		$this->validate([
			"reasonInputs.{$this->reasonModalFor}" => 'required|string|max:500',
		]);

		$dailyTaskId = $this->reasonModalFor;

		$log = DailyTaskLog::firstOrNew([
			'daily_task_id' => $dailyTaskId,
			'user_id' => auth()->id(),
			'date' => $this->selectedDate,
		]);

		$log->is_completed = false;
		$log->reason = $this->reasonInputs[$dailyTaskId];
		$log->completed_at = null;
		$log->save();

		$this->reasonModalFor = null;
		$this->reasonInputs = [];

		unset($this->dailyTasks, $this->pendingCount, $this->completedCount);

		Flux::toast('Alasan disimpan.', variant: 'success');
	}

	public function deleteDailyTask(int $id): void
	{
		if (! $this->canManage()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

			return;
		}

		$dailyTask = $this->taskList->dailyTasks()->findOrFail($id);

		abort_if($dailyTask->created_by !== auth()->id(), 403);

		$dailyTask->delete();

		unset($this->dailyTasks, $this->pendingCount, $this->completedCount);

		Flux::toast('Daily task dihapus.', variant: 'success');
	}

	public function render()
	{
		return view('livewire.project.daily-task-view', [
			'canManage' => $this->canManage(),
		]);
	}
}
