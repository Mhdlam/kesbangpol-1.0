<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;

class Submission extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'organization_id',
        'status_id',
        'assigned_admin_id',
        'title',
        'tracking_code',
        'district',
        'submitted_at',
        'assigned_at',
        'review_started_at',
        'reviewed_at',
        'completed_at',
        'revision_count',
        'is_locked',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'review_started_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    /**
     * Get the prunable model query for automatic history deletion after 30 days.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }

    /**
     * Auto prune expired submissions older than 30 days.
     */
    public static function cleanupExpiredHistory(): void
    {
        static::where('created_at', '<=', now()->subDays(30))->delete();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SubmissionDocument::class);
    }

    public function documentReviews(): HasMany
    {
        return $this->hasMany(SubmissionDocumentReview::class);
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(SubmissionDocumentVersion::class)->orderBy('version', 'desc');
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(AdminInternalNote::class, 'target_id')->where('target_type', 'submission')->orderBy('created_at', 'desc');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SubmissionHistory::class)->orderBy('created_at', 'desc');
    }
}
