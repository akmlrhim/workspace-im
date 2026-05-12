<?php

namespace App\Models\Project;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTaskLog extends Model
{
	protected $fillable = [
		'daily_task_id',
		'user_id',
		'date',
		'is_completed',
		'reason',
		'completed_at',
	];

	protected function casts(): array
	{
		return [
			'date' => 'date',
			'is_completed' => 'boolean',
			'completed_at' => 'datetime',
		];
	}

	public function dailyTask(): BelongsTo
	{
		return $this->belongsTo(DailyTask::class);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
