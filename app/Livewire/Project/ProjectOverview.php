<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Project Overview')]
class ProjectOverview extends Component
{
    public ?Workspace $workspace = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->workspace = Workspace::where('owner_id', $user->id)->first();

        if (! $this->workspace) {
            $this->workspace = Workspace::create([
                'name' => $user->name."'s Workspace",
                'owner_id' => $user->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $this->workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
        }
    }

    public function render()
    {
        $spaceIds = $this->workspace->spaces()->pluck('id');
        $listIds = TaskList::whereIn('space_id', $spaceIds)->pluck('id');

        // Base closure for root-level tasks only (no subtasks)
        $base = fn () => Task::whereIn('task_list_id', $listIds)->whereNull('parent_id');

        $totalSpaces = $spaceIds->count();
        $totalLists = $listIds->count();
        $totalTasks = $base()->count();
        $completedTasks = $base()->whereHas('status', fn ($q) => $q->where('type', 'closed'))->count();
        $inProgressTasks = $base()->whereHas('status', fn ($q) => $q->where('type', 'active'))->count();
        $openTasks = $base()->whereHas('status', fn ($q) => $q->where('type', 'open'))->count();
        $overdueTasks = $base()
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->whereHas('status', fn ($q) => $q->where('type', '!=', 'closed'))
            ->count();

        // Tasks due today
        $dueTodayTasks = $base()
            ->whereDate('due_date', today())
            ->whereHas('status', fn ($q) => $q->where('type', '!=', 'closed'))
            ->count();

        // Tasks by priority
        $tasksByPriority = $base()
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        // Completion rate
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Recently updated tasks (top 8)
        $recentTasks = $base()
            ->with(['status', 'assignees', 'taskList.space'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        // Overdue task list (top 5)
        $overdueTaskList = $base()
            ->with(['status', 'taskList.space'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->whereHas('status', fn ($q) => $q->where('type', '!=', 'closed'))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Lists ranked by task count
        $topLists = TaskList::whereIn('id', $listIds)
            ->withCount('tasks')
            ->orderByDesc('tasks_count')
            ->take(5)
            ->with('space')
            ->get();

        // Tasks due in next 7 days (excluding today and overdue)
        $upcomingTasks = $base()
            ->with(['status', 'taskList'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [today()->addDay(), today()->addDays(7)])
            ->whereHas('status', fn ($q) => $q->where('type', '!=', 'closed'))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        return view('livewire.project.project-overview', [
            'totalSpaces' => $totalSpaces,
            'totalLists' => $totalLists,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'openTasks' => $openTasks,
            'overdueTasks' => $overdueTasks,
            'dueTodayTasks' => $dueTodayTasks,
            'completionRate' => $completionRate,
            'tasksByPriority' => $tasksByPriority,
            'recentTasks' => $recentTasks,
            'overdueTaskList' => $overdueTaskList,
            'topLists' => $topLists,
            'upcomingTasks' => $upcomingTasks,
        ]);
    }
}
