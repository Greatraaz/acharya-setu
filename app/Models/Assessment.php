<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'description',
        'instructions',
        'image',
        'icon',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(AssessmentCategory::class)->orderBy('sort_order')->orderBy('id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scoreBands(): HasMany
    {
        return $this->hasMany(AssessmentScoreBand::class)->orderBy('band_index');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(AssessmentProgress::class, 'assessment_id');
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function imageUrl(): ?string
    {
        return storage_url($this->image);
    }

    public function iconUrl(): ?string
    {
        return storage_url($this->icon);
    }
}
