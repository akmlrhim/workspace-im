<?php

namespace App\Livewire\Project;

use App\Events\DailyTaskUpdated;
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

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo:task-list.{$this->taskList->id},DailyTaskUpdated" => 'onDailyTaskUpdated',
        ];
    }

    public function onDailyTaskUpdated(array $event): void
    {
        if (($event['triggeredBy'] ?? null) === auth()->id()) {
            $this->skipRender();

            return;
        }

        unset($this->dailyTasks, $this->tasksByCreator, $this->pendingCount, $this->completedCount);
    }

    private function broadcastChange(): void
    {
        DailyTaskUpdated::dispatch($this->taskList->id, auth()->id());
    }

    #[Computed]
    public function isToday(): bool
    {
        return $this->selectedDate === today()->toDateString();
    }

    #[Computed]
    public function isSunday(): bool
    {
        return Carbon::parse($this->selectedDate)->isSunday();
    }

    #[Computed]
    public function selectedCarbon(): Carbon
    {
        return Carbon::parse($this->selectedDate);
    }

    #[Computed]
    public function dailyTasks(): Collection
    {
        // Daily tasks only apply on working days (Mon–Sat)
        if ($this->isSunday) {
            return collect();
        }

        $selectedDate = $this->selectedDate;

        return $this->taskList->dailyTasks()
            ->where('is_active', true)
            ->where(function ($q) use ($selectedDate) {
                $q->where(function ($q2) use ($selectedDate) {
                    // Routine tasks appear on every working day from their start date onward
                    $q2->where('type', DailyTask::TYPE_ROUTINE)
                        ->where('date', '<=', $selectedDate);
                })->orWhere(function ($q2) use ($selectedDate) {
                    // On-demand tasks only appear on their specific date
                    $q2->where('type', DailyTask::TYPE_ON_DEMAND)
                        ->where('date', $selectedDate);
                });
            })
            ->with([
                'creator',
                'logs' => fn ($q) => $q->where('date', $selectedDate),
            ])
            ->orderByRaw("CASE WHEN type = 'routine' THEN 0 ELSE 1 END")
            ->orderBy('position')
            ->get();
    }

    #[Computed]
    public function tasksByCreator(): Collection
    {
        return $this->dailyTasks->groupBy('created_by');
    }

    #[Computed]
    public function pendingCount(): int
    {
        return $this->dailyTasks->filter(function (DailyTask $dt) {
            $log = $dt->logs->firstWhere('user_id', $dt->created_by);

            return ! ($log && $log->is_completed);
        })->count();
    }

    #[Computed]
    public function completedCount(): int
    {
        return $this->dailyTasks->count() - $this->pendingCount;
    }

    private function clearComputedCache(): void
    {
        unset($this->dailyTasks, $this->tasksByCreator, $this->pendingCount, $this->completedCount, $this->isToday, $this->isSunday, $this->selectedCarbon);
    }

    public function updatedSelectedDate(): void
    {
        // Clamp future dates to today
        if ($this->selectedDate > today()->toDateString()) {
            $this->selectedDate = today()->toDateString();
        }

        $this->clearComputedCache();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
        $this->clearComputedCache();
    }

    public function nextDay(): void
    {
        if ($this->isToday) {
            return;
        }

        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
        $this->clearComputedCache();
    }

    public function goToToday(): void
    {
        $this->selectedDate = today()->toDateString();
        $this->clearComputedCache();
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

        return $this->taskList->members()->where('users.id', $user->id)->exists();
    }

    public function addDailyTask(string $title, string $description = '', string $type = DailyTask::TYPE_ON_DEMAND): void
    {
        if (! $this->canManage()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

            return;
        }

        if ($this->isSunday) {
            Flux::toast('Daily task tidak tersedia di hari Minggu.', variant: 'warning');

            return;
        }

        $title = trim($title);
        $description = trim($description);

        abort_if($title === '' || strlen($title) > 255, 422);
        abort_if(! in_array($type, [DailyTask::TYPE_ROUTINE, DailyTask::TYPE_ON_DEMAND]), 422);

        if ($type === DailyTask::TYPE_ROUTINE) {
            $maxPosition = $this->taskList->dailyTasks()
                ->where('type', DailyTask::TYPE_ROUTINE)
                ->where('created_by', auth()->id())
                ->max('position') ?? -1;
        } else {
            $maxPosition = $this->taskList->dailyTasks()
                ->where('type', DailyTask::TYPE_ON_DEMAND)
                ->where('date', $this->selectedDate)
                ->max('position') ?? -1;
        }

        $this->taskList->dailyTasks()->create([
            'title' => $title,
            'description' => $description ?: null,
            'created_by' => auth()->id(),
            'position' => $maxPosition + 1,
            'date' => $this->selectedDate,
            'type' => $type,
        ]);

        unset($this->dailyTasks, $this->tasksByCreator, $this->pendingCount, $this->completedCount);

        $this->broadcastChange();
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

        unset($this->dailyTasks, $this->tasksByCreator);

        $this->broadcastChange();
        Flux::toast('Daily task diperbarui.', variant: 'success');
    }

    public function toggleComplete(int $dailyTaskId): void
    {
        if (! $this->canManage()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

            return;
        }

        $dailyTask = $this->taskList->dailyTasks()->findOrFail($dailyTaskId);
        abort_if($dailyTask->created_by !== auth()->id(), 403);

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

        unset($this->dailyTasks, $this->tasksByCreator, $this->pendingCount, $this->completedCount);
        $this->broadcastChange();
    }

    public function openReasonModal(int $dailyTaskId): void
    {
        if (! $this->canManage()) {
            return;
        }

        $dailyTask = $this->taskList->dailyTasks()->findOrFail($dailyTaskId);

        if ($dailyTask->created_by !== auth()->id()) {
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

        unset($this->dailyTasks, $this->tasksByCreator, $this->pendingCount, $this->completedCount);

        $this->broadcastChange();
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

        unset($this->dailyTasks, $this->tasksByCreator, $this->pendingCount, $this->completedCount);

        $this->broadcastChange();
        Flux::toast('Daily task dihapus.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.project.daily-task-view', [
            'canManage' => $this->canManage(),
            'tasksByCreator' => $this->tasksByCreator,
            'isSunday' => $this->isSunday,
        ]);
    }
}
