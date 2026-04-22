<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\Workspace;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Workload Dashboard')]
class WorkloadDashboard extends Component
{
	public string $view = 'task';

	public ?int $workspaceId = null;

	public function mount(): void
	{
		$this->workspaceId = Workspace::where('owner_id', auth()->id())->value('id');
	}

	public function getListeners(): array
	{
		$listeners = [];

		if ($this->workspaceId) {
			$listeners["echo:workspace.{$this->workspaceId},TaskUpdatedGlobal"] = 'onBroadcastUpdate';
			$listeners["echo:workspace.{$this->workspaceId},SpaceUpdated"] = 'onBroadcastUpdate';
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
		$this->view = $view;
	}

	public function render()
	{
		$userId = auth()->id();

		$defaults = [
			'totalTasks' => 0,
			'completedTasks' => 0,
			'overdueTasks' => 0,
			'progressPercent' => 0,
			'memberStats' => collect(),
			'memberTraffic' => [],
			'totalMembers' => 0,
			'taskGroups' => collect(),
			'priorityData' => [],
			'statusData' => [],
		];

		$data = match ($this->view) {
			'member' => array_merge($defaults, $this->getMemberViewData($userId)),
			'task' => array_merge($defaults, $this->getTaskViewData($userId)),
			default => $defaults,
		};

		return view('livewire.project.workload-dashboard', $data);
	}

	private function getMemberViewData(int $userId): array
	{
		$tasks = Task::whereHas('taskList', fn($q) => $q->accessibleBy($userId))
			->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
			->get();

		$memberStats = collect();
		$allAssignees = $tasks->flatMap->assignees->unique('id');

		foreach ($allAssignees as $user) {
			$userTasks = $tasks->filter(fn(Task $t) => $t->assignees->contains('id', $user->id));
			$total = $userTasks->count();
			$completed = $userTasks->filter(fn(Task $t) => $t->status?->type === 'closed')->count();
			$overdue = $userTasks->filter(fn(Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->isPast())->count();
			$progress = $total > 0 ? round(($completed / $total) * 100) : 0;

			$totalSeconds = $userTasks->flatMap->timeTrackings
				->where('user_id', $user->id)
				->sum('duration_seconds');

			$statusBreakdown = $userTasks->groupBy(fn(Task $t) => $t->status?->type ?? 'open');

			$memberStats->push([
				'user' => $user,
				'total' => $total,
				'completed' => $completed,
				'overdue' => $overdue,
				'progress' => $progress,
				'hours' => round($totalSeconds / 3600, 1),
				'open' => $statusBreakdown->get('open', collect())->count(),
				'active' => $statusBreakdown->get('active', collect())->count(),
				'closed' => $statusBreakdown->get('closed', collect())->count(),
			]);
		}

		$memberStats = $memberStats->sortByDesc('total')->values();

		$memberTraffic = $memberStats->take(6)->map(fn($m) => [
			'name' => $m['user']->name,
			'open' => $m['open'],
			'active' => $m['active'],
			'closed' => $m['closed'],
		])->toArray();

		return [
			'memberStats' => $memberStats,
			'memberTraffic' => $memberTraffic,
			'totalMembers' => $allAssignees->count(),
			'totalTasks' => $tasks->count(),
			'completedTasks' => $tasks->filter(fn(Task $t) => $t->status?->type === 'closed')->count(),
			'overdueTasks' => $tasks->filter(fn(Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->isPast())->count(),
		];
	}

	private function getTaskViewData(int $userId): array
	{
		$tasks = Task::whereHas('taskList', fn($q) => $q->accessibleBy($userId))
			->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
			->get();

		$totalTasks = $tasks->count();
		$completedTasks = $tasks->filter(fn(Task $t) => $t->status?->type === 'closed')->count();
		$overdueTasks = $tasks->filter(fn(Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->isPast())->count();
		$progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

		$priorityGroups = $tasks->groupBy('priority');
		$priorityData = [
			['label' => 'Mendesak', 'count' => $priorityGroups->get('urgent', collect())->count(), 'color' => 'bg-red-500'],
			['label' => 'Tinggi', 'count' => $priorityGroups->get('high', collect())->count(), 'color' => 'bg-orange-500'],
			['label' => 'Normal', 'count' => $priorityGroups->get('normal', collect())->count(), 'color' => 'bg-blue-500'],
			['label' => 'Rendah', 'count' => $priorityGroups->get('low', collect())->count(), 'color' => 'bg-zinc-400'],
		];

		$statusGroups = $tasks->groupBy(fn(Task $t) => $t->status?->type ?? 'open');
		$statusData = [
			['label' => 'Belum Dikerjakan', 'count' => $statusGroups->get('open', collect())->count(), 'color' => 'bg-zinc-400'],
			['label' => 'Sedang Dikerjakan', 'count' => $statusGroups->get('active', collect())->count(), 'color' => 'bg-teal-500'],
			['label' => 'Selesai', 'count' => $statusGroups->get('closed', collect())->count(), 'color' => 'bg-emerald-500'],
		];

		$priorityRank = ['urgent' => 0, 'high' => 1, 'normal' => 2, 'low' => 3];

		$taskGroups = $tasks->groupBy(fn(Task $task) => $task->task_list_id)
			->map(function ($groupTasks) use ($priorityRank) {
				$first = $groupTasks->first();
				$total = $groupTasks->count();
				$completed = $groupTasks->filter(fn(Task $t) => $t->status?->type === 'closed')->count();
				$overdue = $groupTasks->filter(fn(Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->isPast())->count();
				$progress = $total > 0 ? round(($completed / $total) * 100) : 0;

				$mappedTasks = $groupTasks->map(function (Task $task) {
					$totalSeconds = $task->timeTrackings->sum('duration_seconds');

					return [
						'id' => $task->id,
						'title' => $task->title,
						'priority' => $task->priority,
						'status_type' => $task->status?->type ?? 'open',
						'status_name' => $task->status?->name ?? 'Terbuka',
						'due_date' => $task->due_date,
						'is_overdue' => $task->status?->type !== 'closed' && $task->due_date && $task->due_date->isPast(),
						'assignees' => $task->assignees,
						'hours' => round($totalSeconds / 3600, 1),
					];
				})->sort(function ($a, $b) use ($priorityRank) {
					return ($b['is_overdue'] <=> $a['is_overdue'])
						?: (($priorityRank[$a['priority']] ?? 99) <=> ($priorityRank[$b['priority']] ?? 99));
				})->values();

				return [
					'name' => $first->taskList?->name ?? 'Tanpa Proyek',
					'space' => $first->taskList?->space?->name,
					'total' => $total,
					'completed' => $completed,
					'overdue' => $overdue,
					'progress' => $progress,
					'tasks' => $mappedTasks,
				];
			})
			->sortByDesc('overdue')
			->values();

		return [
			'totalTasks' => $totalTasks,
			'completedTasks' => $completedTasks,
			'overdueTasks' => $overdueTasks,
			'progressPercent' => $progressPercent,
			'priorityData' => $priorityData,
			'statusData' => $statusData,
			'taskGroups' => $taskGroups,
		];
	}
}
