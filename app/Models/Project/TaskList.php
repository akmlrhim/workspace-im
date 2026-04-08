<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
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
}
