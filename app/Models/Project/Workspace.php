<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
	use GeneratesUuid;

	protected $fillable = ['name', 'slug', 'owner_id'];

	protected static function booted(): void
	{
		static::creating(function (Workspace $workspace) {
			if (empty($workspace->slug)) {
				$workspace->slug = Str::slug($workspace->name) . uniqid();
			}
		});
	}

	public function owner(): BelongsTo
	{
		return $this->belongsTo(User::class, 'owner_id');
	}

	public function members(): HasMany
	{
		return $this->hasMany(WorkspaceMember::class);
	}

	public function spaces(): HasMany
	{
		return $this->hasMany(Space::class)->orderBy('position');
	}

	public function labels(): HasMany
	{
		return $this->hasMany(TaskLabel::class);
	}
}
