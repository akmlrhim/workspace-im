<?php

namespace App\Models\Project;

use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    use HasUuid;

    protected $fillable = ['space_id', 'folder_id', 'name', 'position'];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class)->orderBy('position');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_list_user')->withTimestamps();
    }

    /**
     * Create default statuses for a new list.
     */
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

    /**
     * Scope lists that are accessible by the given user.
     *
     * A list is accessible when the user owns the workspace,
     * is a member of the parent space, is a member of the list,
     * or is assigned to any task in it. Elevated roles
     * (super user / administrator) see every list; managers must
     * be involved in the parent space.
     */
    public function scopeAccessibleBy(Builder $query, int $userId): Builder
    {
        $user = User::find($userId);

        if ($user && $user->canManageAllProjects()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($userId) {
            $q->whereHas('space.workspace', fn (Builder $q2) => $q2->where('owner_id', $userId))
                ->orWhereHas('space.members', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('members', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('tasks.assignees', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('tasks', fn (Builder $q2) => $q2->where('assigned_to', $userId));
        });
    }

    /**
     * Determine whether the given user has visibility on this list.
     */
    public function isAccessibleBy(User $user): bool
    {
        return static::accessibleBy($user->id)->whereKey($this->id)->exists();
    }
}
