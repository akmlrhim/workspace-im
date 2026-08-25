<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeTracking extends Model
{
	use GeneratesUuid;

	protected $fillable = ['task_id', 'user_id', 'started_at', 'stopped_at', 'duration_seconds'];

	protected function casts(): array
	{
		return [
			'started_at' => 'datetime',
			'stopped_at' => 'datetime',
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

	public function getIsRunningAttribute(): bool
	{
		return is_null($this->stopped_at);
	}
}
