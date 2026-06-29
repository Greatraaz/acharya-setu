<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class Message extends Model
{
    use SoftDeletes;
 
    protected $fillable = ['channel_id', 'user_id', 'body', 'parent_id'];
 
    public function user()    { return $this->belongsTo(User::class); }
    public function channel() { return $this->belongsTo(Channel::class); }
    public function replies() { return $this->hasMany(Message::class, 'parent_id')->with('user')->latest(); }
    public function parent()  { return $this->belongsTo(Message::class, 'parent_id'); }
}
