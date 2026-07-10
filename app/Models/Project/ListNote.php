<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListNote extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'task_list_id',
        'created_by',
        'title',
        'content',
        'position',
    ];

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ListNoteAttachment::class);
    }
}
