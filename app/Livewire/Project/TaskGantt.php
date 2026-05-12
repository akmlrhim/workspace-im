<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\TaskList;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskGantt extends Component
{
	public Space $space;

	public TaskList $taskList;

	public ?int $selectedTaskId = null;

	public bool $showTaskDetail = false;

	public string $startDate;

	public string $endDate;

	public function mount(Space $space, TaskList $taskList): void
	{
		$this->space = $space;
		$this->taskList = $taskList;

		// Default: show 4 weeks from today
		$this->startDate = now()->startOfWeek()->format('Y-m-d');
		$this->endDate = now()->startOfWeek()->addWeeks(4)->format('Y-m-d');
	}

	/** @return array<string, string> */
	public function getListeners(): array
	{
		return [
			"echo:task-list.{$this->taskList->id},TaskListUpdated" => 'onBroadcastUpdate',
			"echo:task-list.{$this->taskList->id},TaskUpdated" => 'onBroadcastUpdate',
			'close-task-detail' => 'closeTaskDetail',
			'task-updated' => 'onTaskUpdated',
			'task-deleted' => 'onTaskDeleted',
		];
	}

	public function onBroadcastUpdate(array $event): void
	{
		if (($event['triggeredBy'] ?? null) == auth()->id()) {
			$this->skipRender();

			return;
		}
	}

	public function getTitle(): string
	{
		return $this->taskList->name . ' — Gantt';
	}

	public function previousPeriod(): void
	{
		$this->startDate = Carbon::parse($this->startDate)->subWeeks(2)->format('Y-m-d');
		$this->endDate = Carbon::parse($this->endDate)->subWeeks(2)->format('Y-m-d');
	}

	public function nextPeriod(): void
	{
		$this->startDate = Carbon::parse($this->startDate)->addWeeks(2)->format('Y-m-d');
		$this->endDate = Carbon::parse($this->endDate)->addWeeks(2)->format('Y-m-d');
	}

	public function resetToToday(): void
	{
		$this->startDate = now()->startOfWeek()->format('Y-m-d');
		$this->endDate = now()->startOfWeek()->addWeeks(4)->format('Y-m-d');
	}

	public function openTaskDetail(int $taskId): void
	{
		$this->selectedTaskId = $taskId;
		$this->showTaskDetail = true;
	}

	public function onTaskDeleted(int $taskId): void
	{
		if ($this->selectedTaskId === $taskId) {
			$this->selectedTaskId = null;
		}
		$this->showTaskDetail = false;
	}

	public function closeTaskDetail(): void
	{
		$this->showTaskDetail = false;
	}

	public function onTaskUpdated(): void
	{
		// Re-render
	}

	public function render()
	{
		$start = Carbon::parse($this->startDate);
		$end = Carbon::parse($this->endDate);

		$tasks = $this->taskList->tasks()
			->with(['status', 'assignee'])
			->whereNull('parent_id')
			->whereNotNull('due_date')
			->where(function ($q) use ($start, $end) {
				$q->whereBetween('due_date', [$start, $end])
					->orWhereBetween('created_at', [$start, $end])
					->orWhere(function ($sub) use ($start, $end) {
						$sub->where('created_at', '<=', $start)
							->where('due_date', '>=', $end);
					});
			})
			->orderBy('due_date')
			->get();
		$days = collect(CarbonPeriod::create($start, $end));
		$totalDays = $start->diffInDays($end) + 1;

		return view('livewire.project.task-gantt', [
			'tasks' => $tasks,
			'days' => $days,
			'timelineStart' => $start,
			'timelineEnd' => $end,
			'totalDays' => $totalDays,
		]);
	}
}
