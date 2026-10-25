<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionDocumentVersion extends Model
{
    protected $fillable = [
        'submission_id',
        'document_key',
        'user_id',
        'version',
        'original_name',
        'file_path',
        'file_size',
        'file_mime',
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
