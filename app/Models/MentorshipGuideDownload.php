<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipGuideDownload extends Model
{
    protected $fillable = [
        'mentorship_guide_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'organization',
        'role',
        'ip_address',
    ];

    public function guide(): BelongsTo
    {
        return $this->belongsTo(MentorshipGuide::class, 'mentorship_guide_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
