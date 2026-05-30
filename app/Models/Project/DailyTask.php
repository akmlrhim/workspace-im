<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DailyTask extends Model
{
    use GeneratesUuid;

    public const TYPE_ROUTINE = 'routine';

    public const TYPE_ON_DEMAND = 'on_demand';

    protected $fillable = [
        'task_list_id',
        'created_by',
        'title',
        'description',
        'is_active',
        'position',
        'date',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'date' => 'date:Y-m-d',
        ];
    }

    public function isRoutine(): bool
    {
        return $this->type === self::TYPE_ROUTINE;
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DailyTaskLog::class);
    }

    public function getOrCreateTodayLog(int $userId): DailyTaskLog
    {
        return $this->logs()->firstOrCreate(
            ['user_id' => $userId, 'date' => today()->toDateString()],
            ['is_completed' => false],
        );
    }

    public function isTodayCompletedBy(int $userId): bool
    {
        return $this->logs()
            ->where('user_id', $userId)
            ->where('date', today()->toDateString())
            ->where('is_completed', true)
            ->exists();
    }

    /**
     * @return array{completed: int, total: int}
     */
    public function recentStats(int $userId, int $days = 7): array
    {
        $start = Carbon::today()->subDays($days - 1)->toDateString();

        $logs = $this->logs()
            ->where('user_id', $userId)
            ->where('date', '>=', $start)
            ->get();

        return [
            'completed' => $logs->where('is_completed', true)->count(),
            'total' => $logs->count(),
        ];
    }
}
