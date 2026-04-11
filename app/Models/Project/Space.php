<?php

namespace App\Models\Project;

use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class)->orderBy('position');
    }

    public function lists(): HasMany
    {
        return $this->hasMany(TaskList::class)->orderBy('position');
    }

    public function listsWithoutFolder(): HasMany
    {
        return $this->hasMany(TaskList::class)->whereNull('folder_id')->orderBy('position');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'space_members')->withPivot('role')->withTimestamps();
    }

    public function isMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Scope spaces that are accessible by the given user.
     *
     * A space is accessible when the user owns the workspace,
     * is a member of any list in the space, or is assigned to any task.
     */
    public function scopeAccessibleBy(Builder $query, int $userId): Builder
    {
        $user = User::find($userId);

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
