<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = [
        'name',
        'code',
        'badge_color',
    ];

    public const SUBMITTED = 'submitted';
    public const IN_PROCESS = 'in_process';
    public const REVISION_NEEDED = 'revision_needed';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
