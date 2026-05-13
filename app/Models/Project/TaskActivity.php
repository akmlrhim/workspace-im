<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
	use GeneratesUuid;

	protected $fillable = ['task_id', 'user_id', 'type', 'subject_type', 'subject_id', 'old_value', 'new_value'];

	public function task(): BelongsTo
	{
		return $this->belongsTo(Task::class);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
