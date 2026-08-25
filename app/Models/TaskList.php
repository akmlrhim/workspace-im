<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    use HasUuid;

    protected $fillable = ['space_id', 'name', 'position'];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class)->orderBy('position');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class)->orderBy('position');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ListNote::class)->orderBy('position');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_list_user')->withTimestamps();
    }

    public function createDefaultStatuses(): void
    {
        $defaults = [
            ['name' => 'To Do', 'color' => '#6b7280', 'type' => 'open', 'position' => 0],
            ['name' => 'In Progress', 'color' => '#3b82f6', 'type' => 'active', 'position' => 1],
            ['name' => 'In Review', 'color' => '#f59e0b', 'type' => 'active', 'position' => 2],
            ['name' => 'Done', 'color' => '#10b981', 'type' => 'closed', 'position' => 3],
        ];

        foreach ($defaults as $status) {
            $this->statuses()->create($status);
        }
    }

    public function scopeAccessibleBy(Builder $query, int $userId): Builder
    {
        $user = $userId === (int) auth()->id() ? auth()->user() : User::find($userId);

        if ($user && $user->canManageAllProjects()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($userId) {
            $q->whereHas('space.workspace', fn (Builder $q2) => $q2->where('owner_id', $userId))
                ->orWhereHas('members', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('tasks.assignees', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('tasks', fn (Builder $q2) => $q2->where('assigned_to', $userId));
        });
    }

    public function isAccessibleBy(User $user): bool
    {
        return static::accessibleBy($user->id)->whereKey($this->id)->exists();
    }
}
