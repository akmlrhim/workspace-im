<?php

namespace App\Models\Project;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Space extends Model
{
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
}
