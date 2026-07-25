<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Channel extends Model
{
    use HasFactory;

    public const TYPE_PUBLIC  = 'public';
    public const TYPE_PRIVATE = 'private';

    public const ROLE_ADMIN  = 'admin';
    public const ROLE_MENTOR = 'mentor';
    public const ROLE_MENTEE = 'mentee';

    public const CATEGORIES = [
        'general'      => 'General',
        'ask-mentor'   => 'Ask a Mentor',
        'announcements'=> 'Announcements',
        'career'       => 'Career',
        'tech'         => 'Tech Talk',
        'wellness'     => 'Wellness',
    ];

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'type', 'category',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $channel) {
            if (! empty($channel->slug)) {
                $channel->slug = Str::slug($channel->slug) ?: Str::slug($channel->name);
            }

            if (empty($channel->slug)) {
                $base = Str::slug($channel->name) ?: 'channel';
                $slug = $base;
                $i = 1;
                while (self::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $channel->slug = $slug;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->whereNull('parent_id')->latest();
    }

    public function allMessages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_members')
            ->withPivot(['role', 'last_read_at', 'muted'])
            ->withTimestamps();
    }

    public function mentors(): BelongsToMany
    {
        return $this->members()->wherePivotIn('role', [self::ROLE_MENTOR, self::ROLE_ADMIN]);
    }

    public function mentees(): BelongsToMany
    {
        return $this->members()->wherePivot('role', self::ROLE_MENTEE);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->active()->where(function ($q) use ($user) {
            $q->where('type', self::TYPE_PUBLIC)
              ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id));
        });
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('channel_members.user_id', $user->id)->exists();
    }

    public function isAdmin(User $user): bool
    {
        // Qualify pivot column — both users.role and channel_members.role exist
        return $this->members()
            ->where('channel_members.user_id', $user->id)
            ->wherePivot('role', self::ROLE_ADMIN)
            ->exists();
    }

    public function memberRole(User $user): ?string
    {
        $member = $this->members()->where('channel_members.user_id', $user->id)->first();

        return $member?->pivot?->role;
    }

    public function canAccess(User $user): bool
    {
        if ($this->type === self::TYPE_PUBLIC && $this->is_active) {
            return true;
        }

        return $this->isMember($user);
    }

    public function canPost(User $user): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->type === self::TYPE_PUBLIC) {
            return true;
        }

        return $this->isMember($user);
    }

    public static function roleForUser(User $user): string
    {
        if ($user->isAdmin()) {
            return self::ROLE_ADMIN;
        }

        return $user->isMentor() ? self::ROLE_MENTOR : self::ROLE_MENTEE;
    }

    public function unreadCountFor(User $user): int
    {
        $member = $this->members()->where('channel_members.user_id', $user->id)->first();
        $since  = $member?->pivot?->last_read_at;

        $query = $this->allMessages()->where('user_id', '!=', $user->id);

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        return $query->count();
    }

    public function markRead(User $user): void
    {
        if ($this->isMember($user)) {
            $this->members()->updateExistingPivot($user->id, [
                'last_read_at' => now(),
            ]);
        }
    }

    public function toApiArray(?User $user = null): array
    {
        $data = [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'icon'          => $this->icon,
            'type'          => $this->type,
            'category'      => $this->category,
            'is_active'     => $this->is_active,
            'messages_count'=> $this->all_messages_count ?? $this->allMessages()->count(),
            'members_count' => $this->members_count ?? $this->members()->count(),
            'created_at'    => $this->created_at,
        ];

        if ($user) {
            $data['is_member']    = $this->isMember($user);
            $data['member_role']  = $this->memberRole($user);
            $data['unread_count'] = $this->isMember($user) ? $this->unreadCountFor($user) : 0;
        }

        return $data;
    }
}
