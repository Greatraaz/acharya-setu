<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
        'assign_to_all',
    ];

    protected $casts = [
        'status'        => 'string',
        'assign_to_all' => 'boolean',
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

    public function assignedMentees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assessment_assignments', 'assessment_id', 'mentee_id')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function isVisibleToMentee(User $mentee): bool
    {
        if (! $mentee->isMentee()) {
            return false;
        }

        if (! Schema::hasColumn('assessments', 'assign_to_all')) {
            return true;
        }

        if ($this->assign_to_all) {
            $this->loadMissing('creator');

            if (! $this->created_by || ! $this->creator || $this->creator->isAdmin()) {
                return true;
            }

            if ($this->creator->isMentor()) {
                return User::menteeIdsLinkedToMentor((int) $this->created_by)
                    ->contains((int) $mentee->id);
            }

            return true;
        }

        if (! Schema::hasTable('assessment_assignments')) {
            return false;
        }

        return $this->assignedMentees()->where('users.id', $mentee->id)->exists();
    }

    /**
     * Assessments assigned to this mentee (all-audience or explicit assignment).
     */
    public function scopeVisibleToMentee(Builder $query, User $mentee): Builder
    {
        if (! Schema::hasColumn('assessments', 'assign_to_all')) {
            return $query;
        }

        $mentorIds = User::mentorIdsLinkedToMentee((int) $mentee->id)->all();

        return $query->where(function (Builder $q) use ($mentee, $mentorIds) {
            $q->where(function (Builder $all) use ($mentorIds) {
                $all->where('assign_to_all', true)
                    ->where(function (Builder $who) use ($mentorIds) {
                        $who->whereNull('created_by')
                            ->orWhereHas('creator', fn (Builder $c) => $c->where('role', 'admin'))
                            ->orWhere(function (Builder $mentorAll) use ($mentorIds) {
                                $mentorAll->whereHas('creator', fn (Builder $c) => $c->where('role', 'mentor'));
                                if ($mentorIds === []) {
                                    $mentorAll->whereRaw('1 = 0');
                                } else {
                                    $mentorAll->whereIn('created_by', $mentorIds);
                                }
                            });
                    });
            });

            if (Schema::hasTable('assessment_assignments')) {
                $q->orWhereHas('assignedMentees', fn (Builder $a) => $a->where('users.id', $mentee->id));
            }
        });
    }

    public function imageUrl(): ?string
    {
        return $this->image
            ? url(Storage::url($this->image))
            : null;
    }

    public function iconUrl(): ?string
    {
        return $this->icon
            ? url(Storage::url($this->icon))
            : null;
    }
}
