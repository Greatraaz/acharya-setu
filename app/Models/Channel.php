<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
 
class Channel extends Model
{
    use HasFactory;
 
    protected $fillable = ['name', 'slug', 'description', 'icon', 'type', 'created_by'];
 
    protected static function booted(): void
    {
        static::creating(function ($channel) {
            $channel->slug = Str::slug($channel->name);
        });
    }
 
    public function creator()       { return $this->belongsTo(User::class, 'created_by'); }
    public function messages()      { return $this->hasMany(Message::class)->whereNull('parent_id')->latest(); }
    public function allMessages()   { return $this->hasMany(Message::class); }
    public function members()       { return $this->belongsToMany(User::class, 'channel_members')->withPivot('role')->withTimestamps(); }
 
    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
 
    public function isAdmin(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }
}
