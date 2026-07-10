<?php

namespace App\Models\Project;

use App\Models\Concerns\GeneratesUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListNoteAttachment extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'list_note_id',
        'user_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'is_link',
    ];

    protected $casts = [
        'is_link' => 'boolean',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(ListNote::class, 'list_note_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
