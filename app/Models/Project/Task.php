<?php

namespace App\Models\Project;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'task_list_id', 'task_status_id', 'parent_id', 'title', 'description',
        'priority', 'assigned_to', 'due_date', 'position', 'created_by', 'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_completed' => 'boolean',
        ];
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

    // Legacy single assignee (kept for backward compat)
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Multiple assignees
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
        // Creator
        if ($this->created_by === $user->id) {
            return true;
        }
        // Legacy assigned setting
        if ($this->assigned_to === $user->id) {
            return true;
        }
        // Member of the task via multiple assignees
        if ($this->assignees->contains('id', $user->id)) {
            return true;
        }
        // Workspace Owner
        $workspace = $this->taskList?->space?->workspace;
        if ($workspace && $workspace->owner_id === $user->id) {
            return true;
        }

        return false;
    }
}
