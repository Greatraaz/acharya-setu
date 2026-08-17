<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestion extends Model
{
    public const DEFAULT_OPTIONS = [
        'Not at all',
        'Several days',
        'More than half the days',
        'Nearly every day',
    ];

    protected $fillable = [
        'assessment_id',
        'category_id',
        'question',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'options'    => 'array',
        'sort_order' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }

    public function optionLabels(): array
    {
        $options = $this->options ?? [];

        return [
            0 => $options[0] ?? self::DEFAULT_OPTIONS[0],
            1 => $options[1] ?? self::DEFAULT_OPTIONS[1],
            2 => $options[2] ?? self::DEFAULT_OPTIONS[2],
            3 => $options[3] ?? self::DEFAULT_OPTIONS[3],
        ];
    }
}
