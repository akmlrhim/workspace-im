<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskChecklistItem extends Model
{
	use GeneratesUuid;

	protected $fillable = ['task_checklist_id', 'title', 'is_completed', 'due_date', 'position', 'created_by'];

	protected function casts(): array
	{
		return [
			'is_completed' => 'boolean',
			'due_date' => 'date',
		];
	}

	public function checklist(): BelongsTo
	{
		return $this->belongsTo(TaskChecklist::class, 'task_checklist_id');
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function assignees(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'task_checklist_item_user');
	}

	public function attachments(): HasMany
	{
		return $this->hasMany(TaskAttachment::class);
	}
}
