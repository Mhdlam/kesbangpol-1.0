<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChatMessage extends Model
{
    use Prunable;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'organization_id',
        'submission_id',
        'message',
        'attachment_path',
        'attachment_original_name',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }

    protected function pruning(): void
    {
        if ($this->attachment_path && Storage::disk('local')->exists($this->attachment_path)) {
            Storage::disk('local')->delete($this->attachment_path);
        }
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}

