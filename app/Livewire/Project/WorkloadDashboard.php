<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\TaskList;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Workload Dashboard')]
class WorkloadDashboard extends Component
{
	public string $view = 'project';

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
			'activeLists' => 0,
			'progressPercent' => 0,
			'trafficData' => [],
			'allocationData' => [],
			'allocationFields' => [],
			'atRiskProjects' => collect(),
			'memberStats' => collect(),
			'memberTraffic' => [],
			'totalMembers' => 0,
		];

		if ($this->view === 'project') {
			$data = array_merge($defaults, $this->getProjectViewData($userId));
		} else {
			$data = array_merge($defaults, $this->getMemberViewData($userId));
		}

		return view('livewire.project.workload-dashboard', $data);
	}

	private function getProjectViewData(int $userId): array
	{
		$lists = TaskList::accessibleBy($userId)
			->with(['space', 'statuses', 'tasks.status', 'tasks.assignees', 'tasks.timeTrackings'])
			->get();

		$allTasks = $lists->flatMap->tasks;

		$totalTasks = $allTasks->count();
		$completedTasks = $allTasks->filter(fn(Task $t) => $t->status?->type === 'closed')->count();
		$overdueTasks = $allTasks->filter(fn(Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->isPast())->count();
		$activeLists = $lists->count();
		$progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

		// Task traffic: group by list, then by status type
		$trafficData = $lists->take(6)->map(function (TaskList $list) {
			$statusGroups = $list->tasks->groupBy(fn(Task $t) => $t->status?->type ?? 'open');

			return [
				'name' => $list->name,
				'open' => $statusGroups->get('open', collect())->count(),
				'active' => $statusGroups->get('active', collect())->count(),
				'closed' => $statusGroups->get('closed', collect())->count(),
			];
		});

		// Allocation: tasks created per day this month per list (top 5 lists)
		$startOfMonth = Carbon::now()->startOfMonth();
		$daysInMonth = $startOfMonth->daysInMonth;

		$topLists = $lists->sortByDesc(fn($l) => $l->tasks->count())->take(5)->values();

		$listDayData = $topLists->map(
			fn(TaskList $list) => $list->tasks
				->filter(fn(Task $t) => $t->created_at >= $startOfMonth)
				->groupBy(fn(Task $t) => $t->created_at->day)
		);

		$allocationData = [];
		for ($d = 1; $d <= $daysInMonth; $d++) {
			$row = ['day' => $d];
			foreach ($topLists as $idx => $list) {
				$row["p{$idx}"] = $listDayData[$idx]->get($d, collect())->count();
			}
			$allocationData[] = $row;
		}

		$allocationStyles = [
			['line' => 'text-teal-500', 'area' => 'text-teal-200/40 dark:text-teal-500/20', 'bg' => 'bg-teal-500'],
			['line' => 'text-blue-500', 'area' => 'text-blue-200/40 dark:text-blue-500/20', 'bg' => 'bg-blue-500'],
			['line' => 'text-amber-500', 'area' => 'text-amber-200/40 dark:text-amber-500/20', 'bg' => 'bg-amber-500'],
			['line' => 'text-rose-500', 'area' => 'text-rose-200/40 dark:text-rose-500/20', 'bg' => 'bg-rose-500'],
			['line' => 'text-purple-500', 'area' => 'text-purple-200/40 dark:text-purple-500/20', 'bg' => 'bg-purple-500'],
		];

		$allocationFields = $topLists->map(fn(TaskList $list, int $idx) => [
			'field' => "p{$idx}",
			'name' => Str::limit($list->name, 15),
			'color' => $allocationStyles[$idx]['line'] ?? 'text-zinc-500',
			'areaColor' => $allocationStyles[$idx]['area'] ?? 'text-zinc-200/40',
			'bgColor' => $allocationStyles[$idx]['bg'] ?? 'bg-zinc-500',
		])->toArray();

		// At-risk projects (lists with overdue or high-priority incomplete tasks)
		$atRiskProjects = $lists->map(function (TaskList $list) {
			$tasks = $list->tasks;
			$total = $tasks->count();
			$completed = $tasks->filter(fn(Task $t) => $t->status?->type === 'closed')->count();
			$overdue = $tasks->filter(fn(Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->isPast())->count();
			$progress = $total > 0 ? round(($completed / $total) * 100) : 0;

			$nearestDue = $tasks->where('is_completed', false)
				->filter(fn(Task $t) => $t->due_date !== null)
				->sortBy('due_date')
				->first();

			$totalSeconds = $tasks->flatMap->timeTrackings->sum('duration_seconds');
			$hours = round($totalSeconds / 3600, 1);

			$lead = $tasks->flatMap->assignees->unique('id')->first();

			if ($overdue > 2) {
				$status = 'at-risk';
			} elseif ($overdue > 0 || $progress < 30) {
				$status = 'stuck';
			} else {
				$status = 'on-track';
			}

			return [
				'name' => $list->name,
				'space' => $list->space?->name,
				'progress' => $progress,
				'lead' => $lead,
				'deadline' => $nearestDue?->due_date,
				'status' => $status,
				'hours' => $hours,
				'total' => $total,
				'completed' => $completed,
				'overdue' => $overdue,
			];
		})->sortBy(fn($p) => match ($p['status']) {
			'at-risk' => 0,
			'stuck' => 1,
			default => 2,
		})->values();

		return [
			'totalTasks' => $totalTasks,
			'completedTasks' => $completedTasks,
			'overdueTasks' => $overdueTasks,
			'activeLists' => $activeLists,
			'progressPercent' => $progressPercent,
			'trafficData' => $trafficData->toArray(),
			'allocationData' => $allocationData,
			'allocationFields' => $allocationFields,
			'atRiskProjects' => $atRiskProjects,
		];
	}

	private function getMemberViewData(int $userId): array
	{
		$tasks = Task::whereHas('taskList', fn($q) => $q->accessibleBy($userId))
			->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
			->get();

		// Group tasks by assignee
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

		// Traffic data per member (top 6)
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
}
