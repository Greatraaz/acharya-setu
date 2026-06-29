<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
 
class JobListing extends Model
{
    use SoftDeletes;
 
    protected $fillable = [
        'title', 'slug', 'department', 'location', 'location_type', 'job_type',
        'experience_level', 'salary_min', 'salary_max', 'salary_currency',
        'salary_period', 'salary_hidden', 'description', 'responsibilities',
        'requirements', 'benefits', 'skills', 'apply_url', 'apply_email',
        'deadline', 'openings', 'status', 'is_featured', 'posted_by', 'published_at',
    ];
 
    protected $casts = [
        'skills'       => 'array',
        'salary_hidden'=> 'boolean',
        'is_featured'  => 'boolean',
        'deadline'     => 'date',
        'published_at' => 'datetime',
        'salary_min'   => 'decimal:2',
        'salary_max'   => 'decimal:2',
    ];
 
    const STATUS_DRAFT   = 'draft';
    const STATUS_ACTIVE  = 'active';
    const STATUS_PAUSED  = 'paused';
    const STATUS_CLOSED  = 'closed';
 
    const JOB_TYPES = [
        'full_time'  => 'Full Time',
        'part_time'  => 'Part Time',
        'contract'   => 'Contract',
        'internship' => 'Internship',
        'freelance'  => 'Freelance',
    ];
 
    const EXPERIENCE_LEVELS = [
        'entry'     => 'Entry Level',
        'mid'       => 'Mid Level',
        'senior'    => 'Senior',
        'lead'      => 'Lead',
        'executive' => 'Executive',
    ];
 
    const LOCATION_TYPES = [
        'onsite' => 'On-site',
        'remote' => 'Remote',
        'hybrid' => 'Hybrid',
    ];
 
    protected static function booted(): void
    {
        static::creating(function (JobListing $job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title) . '-' . Str::random(5);
            }
        });
    }
 
    // ── Relationships ─────────────────────────────────────────
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
 
 
    // ── Scopes ────────────────────────────────────────────────
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }
 
    public function scopeByDepartment(Builder $q, string $dept): Builder
    {
        return $q->where('department', $dept);
    }
 
    // ── Accessors ─────────────────────────────────────────────
    public function getSalaryRangeAttribute(): string
    {
        if ($this->salary_hidden) return 'Competitive';
        $sym = config_val('currency_symbol', '₹');
        $period = $this->salary_period === 'yearly' ? '/yr' : '/mo';
        if ($this->salary_min && $this->salary_max) {
            return $sym . number_format($this->salary_min / 100000, 1) . 'L – '
                 . $sym . number_format($this->salary_max / 100000, 1) . 'L' . $period;
        }
        if ($this->salary_min) return $sym . number_format($this->salary_min / 100000, 1) . 'L+' . $period;
        return 'Competitive';
    }
 
    public function getIsExpiredAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }
 
    public function getJobTypeLabelAttribute(): string
    {
        return self::JOB_TYPES[$this->job_type] ?? ucfirst($this->job_type);
    }
 
    public function getExperienceLabelAttribute(): string
    {
        return self::EXPERIENCE_LEVELS[$this->experience_level] ?? ucfirst($this->experience_level);
    }
 
    public function getLocationTypeLabelAttribute(): string
    {
        return self::LOCATION_TYPES[$this->location_type] ?? ucfirst($this->location_type);
    }
 
    // ── Publish helper ────────────────────────────────────────
    public function publish(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE, 'published_at' => now()]);
    }
 
    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }
}
 
