<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'abbreviation',
        'registration_number',
        'university',
        'address',
        'district',
        'category',
        'leader_name',
        'secretary_name',
        'treasurer_name',
        'is_religious',
        'status',
        'archived_at',
    ];

    protected $casts = [
        'is_religious' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(AdminInternalNote::class, 'target_id')->where('target_type', 'organization')->orderBy('created_at', 'desc');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'organization_id');
    }
}
