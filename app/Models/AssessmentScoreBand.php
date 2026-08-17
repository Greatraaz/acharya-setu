<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScoreBand extends Model
{
    protected $fillable = [
        'assessment_id',
        'band_index',
        'range_from',
        'range_to',
        'heading',
        'description',
    ];

    protected $casts = [
        'band_index' => 'integer',
        'range_from' => 'integer',
        'range_to'   => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
