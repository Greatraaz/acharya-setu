<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightEventRegistration extends Model
{
    protected $fillable = [
        'insight_event_id',
        'full_name',
        'email',
        'company',
        'phone',
        'user_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(InsightEvent::class, 'insight_event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
