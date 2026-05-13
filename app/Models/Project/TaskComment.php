<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskComment extends Model
{
	use GeneratesUuid;

	protected $fillable = ['task_id', 'user_id', 'body', 'parent_id'];

	public function task(): BelongsTo
	{
		return $this->belongsTo(Task::class);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function parent(): BelongsTo
	{
		return $this->belongsTo(TaskComment::class, 'parent_id');
	}

	public function replies(): HasMany
	{
		return $this->hasMany(TaskComment::class, 'parent_id')->orderBy('created_at');
	}

	public function attachments(): HasMany
	{
		return $this->hasMany(TaskAttachment::class);
	}
}
