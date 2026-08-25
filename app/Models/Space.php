<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Space extends Model
{
    use HasUuid;

    protected $fillable = ['workspace_id', 'name', 'slug', 'color', 'icon', 'position'];

    protected static function booted(): void
    {
        static::creating(function (Space $space) {
            if (empty($space->slug)) {
                $space->slug = Str::slug($space->name);
            }
        });

        static::deleting(function (Space $space) {
            $paths = TaskAttachment::query()
                ->whereHas('task.taskList', fn ($q) => $q->where('space_id', $space->id))
                ->where('is_link', false)
                ->whereNotNull('path')
                ->pluck('path');

            if ($paths->isNotEmpty()) {
                Storage::disk('public')->delete($paths->all());
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(TaskList::class)->orderBy('position');
    }

    public function scopeAccessibleBy(Builder $query, int $userId): Builder
    {
        $user = $userId === (int) auth()->id() ? auth()->user() : User::find($userId);

        if ($user && $user->canManageAllProjects()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($userId) {
            $q->whereHas('workspace', fn (Builder $q2) => $q2->where('owner_id', $userId))
                ->orWhereHas('lists.members', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('lists.tasks.assignees', fn (Builder $q2) => $q2->where('users.id', $userId))
                ->orWhereHas('lists.tasks', fn (Builder $q2) => $q2->where('assigned_to', $userId));
        });
    }
}
