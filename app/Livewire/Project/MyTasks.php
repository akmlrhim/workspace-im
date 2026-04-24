<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\Workspace;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Tasks')]
class MyTasks extends Component
{
	public string $filterPriority = '';

	public ?int $selectedTaskId = null;

	public bool $showTaskDetail = false;

	public ?int $workspaceId = null;

	public string $view = 'list';

	public int $currentYear;

	public int $currentMonth;

	public function mount(): void
	{
		$this->workspaceId = Workspace::where('owner_id', auth()->id())->value('id');
		$this->currentYear = now()->year;
		$this->currentMonth = now()->month;
	}

	/** @return array<string, string> */
	public function getListeners(): array
	{
		$listeners = [
			'close-task-detail' => 'closeTaskDetail',
			'task-updated' => 'onTaskUpdated',
		];

		if ($this->workspaceId) {
			$listeners["echo:workspace.{$this->workspaceId},TaskUpdatedGlobal"] = 'onBroadcastUpdate';
		}

		return $listeners;
	}

	public function onBroadcastUpdate(array $event): void
	{
		if (($event['triggeredBy'] ?? null) == auth()->id()) {
			$this->skipRender();

			return;
		}
	}

	public function switchView(string $view): void
	{
		if (in_array($view, ['list', 'calendar'], true)) {
			$this->view = $view;
		}
	}

	public function previousMonth(): void
	{
		$date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
		$this->currentYear = $date->year;
		$this->currentMonth = $date->month;
	}

	public function nextMonth(): void
	{
		$date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
		$this->currentYear = $date->year;
		$this->currentMonth = $date->month;
	}

	public function goToToday(): void
	{
		$this->currentYear = now()->year;
		$this->currentMonth = now()->month;
	}

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

	public function onTaskUpdated(): void
	{
		// Re-render
	}

	public function render()
	{
		$userId = auth()->id();

		$baseQuery = Task::where(function ($q) use ($userId) {
			$q->where('assigned_to', $userId)
				->orWhereHas('assignees', fn($sub) => $sub->where('user_id', $userId));
		})
			->with(['status', 'taskList.space', 'assignees'])
			->whereNull('parent_id');

		if ($this->filterPriority) {
			$baseQuery->where('priority', $this->filterPriority);
		}

		if ($this->view === 'calendar') {
			return $this->renderCalendar($baseQuery);
		}

		return $this->renderList($baseQuery);
	}

	private function renderList($baseQuery)
	{
		$tasks = $baseQuery
			->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
			->get();

		$typeOrder = ['open' => 0, 'active' => 1, 'closed' => 2];

		$grouped = $tasks
			->filter(fn($t) => $t->status !== null)
			->groupBy(fn($t) => mb_strtolower(trim($t->status->name)))
			->map(function ($statusTasks, $key) {
				$firstStatus = $statusTasks->first()->status;

				return [
					'status' => (object) [
						'id' => $key,
						'name' => $firstStatus->name,
						'color' => $firstStatus->color,
						'type' => $firstStatus->type,
					],
					'tasks' => $statusTasks->values(),
				];
			})
			->sortBy(fn($group) => sprintf(
				'%d-%s',
				$typeOrder[$group['status']->type] ?? 99,
				mb_strtolower($group['status']->name),
			))
			->values();

		$ungrouped = $tasks->filter(fn($t) => $t->status === null)->values();

		return view('livewire.project.my-tasks', [
			'grouped' => $grouped,
			'ungrouped' => $ungrouped,
			'totalCount' => $tasks->count(),
			'weeks' => [],
			'monthLabel' => '',
		]);
	}

	private function renderCalendar($baseQuery)
	{
		$monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1);
		$monthEnd = $monthStart->copy()->endOfMonth();
		$calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
		$calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

		$totalCount = (clone $baseQuery)->count();

		$tasksByDay = (clone $baseQuery)
			->whereNotNull('due_date')
			->whereBetween('due_date', [$calendarStart, $calendarEnd])
			->orderBy('due_date')
			->get()
			->groupBy(fn($t) => $t->due_date->format('Y-m-d'));

		$weeks = [];
		$currentDay = $calendarStart->copy();

		while ($currentDay <= $calendarEnd) {
			$week = [];
			for ($i = 0; $i < 7; $i++) {
				$dayStr = $currentDay->format('Y-m-d');
				$week[] = [
					'date' => $currentDay->copy(),
					'isCurrentMonth' => $currentDay->month === $this->currentMonth,
					'isToday' => $currentDay->isToday(),
					'tasks' => $tasksByDay->get($dayStr, collect()),
				];
				$currentDay->addDay();
			}
			$weeks[] = $week;
		}

		return view('livewire.project.my-tasks', [
			'grouped' => collect(),
			'ungrouped' => collect(),
			'totalCount' => $totalCount,
			'weeks' => $weeks,
			'monthLabel' => $monthStart->format('F Y'),
		]);
	}
}
