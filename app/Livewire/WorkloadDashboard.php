<?php

namespace App\Livewire;

use App\Livewire\Concerns\BuildsMemberWorkload;
use App\Livewire\Concerns\BuildsTaskWorkload;
use App\Livewire\Concerns\FiltersWorkloadTasks;
use App\Livewire\Concerns\SummarizesTaskStatus;
use App\Models\Space;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Workload Dashboard')]
class WorkloadDashboard extends Component
{
    use BuildsMemberWorkload;
    use BuildsTaskWorkload;
    use FiltersWorkloadTasks;
    use SummarizesTaskStatus;

    public string $view = 'task';

    public string $selectedMonth = '';

    public ?int $selectedSpaceId = null;

    public ?int $selectedMemberId = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            'echo:project,TaskListUpdated' => 'onBroadcastUpdate',
            'echo:project,TaskUpdated' => 'onBroadcastUpdate',
            'echo:project,SpaceUpdated' => 'onBroadcastUpdate',
        ];
    }

    public function onBroadcastUpdate(): void {}

    #[Computed]
    public function spaces()
    {
        return Space::accessibleBy(auth()->id())->orderBy('position')->get();
    }

    #[Computed]
    public function members()
    {
        return $this->baseTaskQuery()
            ->with('assignees:id,name')
            ->get()
            ->flatMap
            ->assignees
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function switchView(string $view): void
    {
        $this->view = $view;
    }

    public function selectSpace(?int $spaceId): void
    {
        $this->selectedSpaceId = $spaceId;

        $this->selectedMemberId = null;
    }

    public function updatedSelectedMonth(): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $this->selectedMonth)) {
            $this->selectedMonth = now()->format('Y-m');
        }

        unset($this->members);

        if ($this->selectedMemberId !== null && ! $this->members->contains('id', $this->selectedMemberId)) {
            $this->selectedMemberId = null;
        }
    }

    public function render()
    {
        $defaults = [
            'totalTasks' => 0,
            'completedTasks' => 0,
            'completedOnTime' => 0,
            'completedLate' => 0,
            'completedNoDeadline' => 0,
            'latePercent' => 0,
            'noDeadlinePercent' => 0,
            'overdueTasks' => 0,
            'overduePercent' => 0,
            'progressPercent' => 0,

            'memberStats' => collect(),
            'memberTraffic' => [],
            'totalMembers' => 0,
            'taskGroups' => collect(),
            'spaces' => $this->spaces,
        ];

        $data = match ($this->view) {
            'member' => array_merge($defaults, $this->getMemberViewData()),
            'task' => array_merge($defaults, $this->getTaskViewData()),
            default => $defaults,
        };

        return view('livewire.workload-dashboard', $data);
    }
}
