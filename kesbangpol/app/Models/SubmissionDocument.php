<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionDocument extends Model
{
    protected $fillable = [
        'submission_id',
        'user_id',
        'document_key',
        'original_name',
        'file_path',
        'file_size',
        'file_mime',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
