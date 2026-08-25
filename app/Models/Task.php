<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    use GeneratesUuid;

    protected static function booted(): void
    {
        static::deleting(function (Task $task) {
            $task->attachments()
                ->where('is_link', false)
                ->whereNotNull('path')
                ->pluck('path')
                ->each(fn (string $path) => Storage::disk('public')->delete($path));
        });

        static::saving(function (Task $task) {
            if (! $task->exists || $task->isDirty('task_status_id')) {
                $task->syncCompletedAt();
            }
        });
    }

    protected $fillable = [
        'task_list_id',
        'task_status_id',
        'parent_id',
        'title',
        'description',
        'priority',
        'assigned_to',
        'due_date',
        'completed_at',
        'position',
        'created_by',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'is_completed' => 'boolean',
        ];
    }

    public function syncCompletedAt(): void
    {
        $isClosed = TaskStatus::whereKey($this->task_status_id)->value('type') === 'closed';

        if ($isClosed) {
            if ($this->completed_at === null) {
                $this->completed_at = now();
            }
        } else {
            $this->completed_at = null;
        }
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label_task');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_id')->orderByDesc('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderByDesc('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->orderByDesc('created_at');
    }

    public function timeTrackings(): HasMany
    {
        return $this->hasMany(TimeTracking::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('position');
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '#ef4444',
            'high' => '#f97316',
            'normal' => '#3b82f6',
            'low' => '#6b7280',
            default => '#6b7280',
        };
    }

    public function canBeManagedBy(User $user): bool
    {
        if ($user->canManageAllProjects()) {
            return true;
        }

        $workspace = $this->taskList?->space?->workspace;
        if ($workspace && $workspace->owner_id === $user->id) {
            return true;
        }

        if ($user->isManager() && $this->taskList && $this->taskList->isAccessibleBy($user)) {
            return true;
        }

        if ($this->created_by === $user->id) {
            return true;
        }

        if ($this->assigned_to === $user->id) {
            return true;
        }

        if ($this->assignees->contains('id', $user->id)) {
            return true;
        }

        return false;
    }

    public function scopeExcludeNotes(Builder $query): Builder
    {
        return $query->whereDoesntHave('status', fn ($q) => $q->whereRaw('LOWER(name) = ?', ['note']));
    }
}
