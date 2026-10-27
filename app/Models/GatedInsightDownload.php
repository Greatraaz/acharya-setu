<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatedInsightDownload extends Model
{
    protected $fillable = [
        'gated_insight_item_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'organization',
        'role',
        'ip_address',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(GatedInsightItem::class, 'gated_insight_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
