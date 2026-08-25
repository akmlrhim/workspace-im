<?php

namespace App\Livewire;

use App\Events\DailyTaskUpdated;
use App\Livewire\Concerns\BroadcastsChangesSafely;
use App\Livewire\Concerns\LogsDailyTaskCompletion;
use App\Livewire\Concerns\NavigatesDailyDate;
use App\Models\DailyTask;
use App\Models\Space;
use App\Models\TaskList;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DailyTaskView extends Component
{
    use BroadcastsChangesSafely;
    use LogsDailyTaskCompletion;
    use NavigatesDailyDate;

    public Space $space;

    public TaskList $taskList;

    private ?bool $canManageCache = null;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
        $this->selectedDate = today()->toDateString();
    }

    /**
     * @return array<string, string>
     */
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

        $this->clearTaskCache();
    }

    private function broadcastChange(): void
    {
        $this->broadcastSafely(fn () => DailyTaskUpdated::dispatch($this->taskList->id, auth()->id()));
    }

    private function clearTaskCache(): void
    {
        unset($this->dailyTasks, $this->pendingCount, $this->completedCount);
    }

    #[Computed]
    public function dailyTasks(): Collection
    {
        $selectedDate = $this->selectedDate;

        return $this->taskList->dailyTasks()
            ->where('is_active', true)
            ->where(function ($q) use ($selectedDate) {
                $q->where(function ($q2) use ($selectedDate) {
                    $q2->where('type', DailyTask::TYPE_ROUTINE)
                        ->where('date', '<=', $selectedDate);
                })->orWhere(function ($q2) use ($selectedDate) {
                    $q2->where('type', DailyTask::TYPE_ON_DEMAND)
                        ->where('date', $selectedDate);
                });
            })
            ->with([
                'logs' => fn ($q) => $q->where('date', $selectedDate)->with('user'),
            ])
            ->orderByRaw("CASE WHEN type = 'routine' THEN 0 ELSE 1 END")
            ->orderBy('position')
            ->get();
    }

    #[Computed]
    public function pendingCount(): int
    {
        $myId = auth()->id();

        return $this->dailyTasks->filter(function (DailyTask $dt) use ($myId) {
            $log = $dt->logs->firstWhere('user_id', $myId);

            return ! ($log && $log->is_completed);
        })->count();
    }

    #[Computed]
    public function completedCount(): int
    {
        return $this->dailyTasks->count() - $this->pendingCount;
    }

    private function canManage(): bool
    {
        return $this->canManageCache ??= $this->resolveCanManage();
    }

    private function resolveCanManage(): bool
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

    private function guardManage(): bool
    {
        if (! $this->canManage()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah ini.', variant: 'danger');

            return false;
        }

        return true;
    }

    public function addDailyTask(string $title, string $description = '', string $type = DailyTask::TYPE_ON_DEMAND): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $title = trim($title);
        $description = trim($description);

        abort_if($title === '' || strlen($title) > 255, 422);
        abort_if(! in_array($type, [DailyTask::TYPE_ROUTINE, DailyTask::TYPE_ON_DEMAND]), 422);

        $this->taskList->dailyTasks()->create([
            'title' => $title,
            'description' => $description ?: null,
            'created_by' => auth()->id(),
            'position' => $this->nextPositionFor($type),
            'date' => $this->selectedDate,
            'type' => $type,
        ]);

        $this->clearTaskCache();

        $this->broadcastChange();
        Flux::toast('Daily task ditambahkan.', variant: 'success');
    }

    public function saveEdit(int $id, string $title, string $description = ''): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $title = trim($title);
        $description = trim($description);

        abort_if($title === '' || strlen($title) > 255, 422);

        $this->taskList->dailyTasks()->findOrFail($id)->update([
            'title' => $title,
            'description' => $description ?: null,
        ]);

        $this->clearTaskCache();

        $this->broadcastChange();
        Flux::toast('Daily task diperbarui.', variant: 'success');
    }

    public function deleteDailyTask(int $id): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $this->taskList->dailyTasks()->findOrFail($id)->delete();

        $this->clearTaskCache();

        $this->broadcastChange();
        Flux::toast('Daily task dihapus.', variant: 'success');
    }

    private function nextPositionFor(string $type): int
    {
        $query = $this->taskList->dailyTasks()
            ->where('type', $type)
            ->where('created_by', auth()->id());

        if ($type === DailyTask::TYPE_ON_DEMAND) {
            $query->where('date', $this->selectedDate);
        }

        return ($query->max('position') ?? -1) + 1;
    }

    public function render()
    {
        return view('livewire.daily-task-view', [
            'canManage' => $this->canManage(),
        ]);
    }
}
