<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskChecklist extends Model
{
	use GeneratesUuid;

	protected $fillable = ['task_id', 'name', 'position'];

	public function task(): BelongsTo
	{
		return $this->belongsTo(Task::class);
	}

	public function items(): HasMany
	{
		return $this->hasMany(TaskChecklistItem::class)->orderBy('position');
	}
}
