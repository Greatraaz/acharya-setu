<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
 
class EducationStream extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'color', 'description', 'is_active', 'sort_order'];
 
    protected $casts = ['is_active' => 'boolean'];
 
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
    }
 
    public function months(): HasMany
    {
        return $this->hasMany(CurriculumMonth::class, 'stream_id')->orderBy('month_number');
    }
 
    public function enrollments(): HasMany
    {
        return $this->hasMany(MenteeEnrollment::class, 'stream_id');
    }
 
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
 
    public function getTotalTasksAttribute(): int
    {
        return CurriculumTask::whereHas('week.month', fn($q) => $q->where('stream_id', $this->id))->count();
    }
 
    public function getTotalMcqsAttribute(): int
    {
        return CurriculumMcq::whereHas('week.month', fn($q) => $q->where('stream_id', $this->id))->count();
    }
}
 