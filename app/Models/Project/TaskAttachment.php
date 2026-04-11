<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use GeneratesUuid;

    protected $fillable = ['task_id', 'task_comment_id', 'task_checklist_item_id', 'user_id', 'filename', 'path', 'mime_type', 'size'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'task_comment_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(TaskChecklistItem::class, 'task_checklist_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
