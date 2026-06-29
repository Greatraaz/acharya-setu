<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class WellnessResponse extends Model
{
    protected $fillable = ['survey_id', 'user_id'];
 
    public function user()    { return $this->belongsTo(User::class); }
    public function survey()  { return $this->belongsTo(WellnessSurvey::class, 'survey_id'); }
    public function answers() { return $this->hasMany(WellnessAnswer::class, 'response_id'); }
}
