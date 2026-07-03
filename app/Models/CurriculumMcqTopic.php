<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumMcqTopic extends Model
{
    protected $fillable = [
        'week_id',
        'mentee_id',
        'name',
        'description',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function week(): BelongsTo
    {
        return $this->belongsTo(CurriculumWeek::class, 'week_id');
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function mcqs(): HasMany
    {
        return $this->hasMany(CurriculumMcq::class, 'topic_id')->orderBy('order_index');
    }
}
