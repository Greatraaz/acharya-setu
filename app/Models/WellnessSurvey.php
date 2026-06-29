<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class WellnessSurvey extends Model
{
    protected $fillable = ['title', 'description', 'is_active', 'expires_at', 'created_by'];
    protected $casts    = ['is_active' => 'boolean', 'expires_at' => 'date'];
 
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function questions() { return $this->hasMany(WellnessQuestion::class, 'survey_id')->orderBy('order'); }
    public function responses() { return $this->hasMany(WellnessResponse::class, 'survey_id'); }
 
    public function hasResponded(User $user): bool
    {
        return $this->responses()->where('user_id', $user->id)->exists();
    }
 
    public function getResponseCountAttribute(): int
    {
        return $this->responses()->count();
    }
}
