<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use GeneratesUuid;

    protected $fillable = ['space_id', 'name', 'position'];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(TaskList::class)->orderBy('position');
    }
}
