<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDeadlineNotification extends Model
{
	public $timestamps = false;

	protected $fillable = ['task_id', 'user_id', 'due_date', 'notified_at'];

	protected function casts(): array
	{
		return [
			'due_date' => 'date',
			'notified_at' => 'datetime',
		];
	}

	public function task(): BelongsTo
	{
		return $this->belongsTo(Task::class);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
