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

    /** @var list<string> */
    public const BANNED_WORDS = [
        'fuck', 'fucking', 'shit', 'bitch', 'asshole', 'bastard', 'slut', 'whore', 'dick', 'pussy', 'cunt', 'faggot', 'retard', 'penis',
        'chutiya', 'chutiye', 'madarchod', 'behenchod', 'bhenchod', 'mc', 'bc', 'gandu', 'gaand', 'randi', 'harami', 'kamina', 'lund', 'lauda',
        'loda', 'chod', 'chodu', 'jhant', 'suar', 'chut', 'chuda', 'gand', 'tatte', 'tatta', 'moot', 'tatti', 'peshab', 'paad', 'radua', 'bhadua',
        'kill you', 'kill myself', 'suicide', 'rape', 'balatkar', 'maar dunga', 'faad dunga', 'click here', 'free money', 'earn from home', 'bit.ly', 'wa.me', 'saale', 'sale',
    ];

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'type', 'category',
        'image_path', 'video_path',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url', 'video_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->image_path);
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->video_path);
    }

    private function publicMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'upload/')) {
            return url($path);
        }

        return url('storage/'.ltrim($path, '/'));
    }

    public static function storeChannelMedia(\Illuminate\Http\Request $request, int $channelId): array
    {
        $attrs = [];

        if ($request->hasFile('image')) {
            $file      = $request->file('image');
            $directory = public_path('upload/community/'.$channelId.'/channel');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $ext            = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $fileName       = 'ch_img_'.time().'_'.uniqid().'.'.$ext;
            $file->move($directory, $fileName);
            $attrs['image_path'] = 'upload/community/'.$channelId.'/channel/'.$fileName;
        }

        if ($request->hasFile('video')) {
            $file      = $request->file('video');
            $directory = public_path('upload/community/'.$channelId.'/channel');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $ext            = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'mp4');
            $fileName       = 'ch_vid_'.time().'_'.uniqid().'.'.$ext;
            $file->move($directory, $fileName);
            $attrs['video_path'] = 'upload/community/'.$channelId.'/channel/'.$fileName;
        }

        return $attrs;
    }

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
        return $this->hasMany(Message::class)->whereNull('parent_id')->oldest();
    }

    /**
     * All channel messages visible to a user (top-level + replies), oldest first.
     */
    public function messagesForUser(User $user)
    {
        return $this->allMessages()
            ->visibleToUser($user)
            ->with([
                'user',
                'parent' => fn ($q) => $q->with('user'),
            ])
            ->oldest();
    }

    /** Paginate messages oldest→newest; defaults to the last page so latest appear at the bottom. */
    public function paginateMessagesForUser(User $user, int $perPage = 30)
    {
        $query = $this->messagesForUser($user);

        $total = (clone $query)->toBase()->getCountForPagination();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) request()->input('page', $lastPage);
        $page = max(1, min($page, $lastPage));

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    public static function storeValidationRules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:channels,name',
            'slug'        => 'nullable|string|max:120|unique:channels,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:10',
            'type'        => 'required|in:public,private',
            'category'    => 'nullable|string|max:50',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ];
    }

    public static function adminStoreValidationRules(): array
    {
        $rules = self::storeValidationRules();
        $rules['type'] = 'sometimes|in:public,private';

        return $rules;
    }

    /** @return list<string> */
    public static function bannedWordsList(): array
    {
        static $words = null;

        if ($words === null) {
            $words = collect(self::BANNED_WORDS)
                ->map(fn ($word) => mb_strtolower(trim((string) $word)))
                ->filter(fn ($word) => $word !== '')
                ->unique()
                ->sortByDesc(fn ($word) => mb_strlen($word))
                ->values()
                ->all();
        }

        return $words;
    }

    public static function findBannedWordIn(?string $text): ?string
    {
        $text = mb_strtolower(trim((string) $text));

        if ($text === '') {
            return null;
        }

        foreach (self::bannedWordsList() as $word) {
            if (str_contains($word, ' ')) {
                if (str_contains($text, $word)) {
                    return $word;
                }

                continue;
            }

            $pattern = '/\b'.preg_quote($word, '/').'\b/ui';
            if (preg_match($pattern, $text)) {
                return $word;
            }
        }

        return null;
    }

    public static function validateMessageBody(?string $body): void
    {
        self::validateContent($body, 'body');
    }

    public static function abusiveContentMessage(string $field = 'body'): string
    {
        return match ($field) {
            'name'        => 'The channel name contains language that isn\'t allowed. Please remove offensive or abusive words.',
            'description' => 'The description contains language that isn\'t allowed. Please remove offensive or abusive words.',
            default       => 'Your message contains language that isn\'t allowed in this community. Please remove offensive or abusive words and try again.',
        };
    }

    public static function validateContent(?string $text, string $field = 'body'): void
    {
        if (self::findBannedWordIn($text) !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => [self::abusiveContentMessage($field)],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public static function validateChannelTextFields(array $fields): void
    {
        foreach (['name', 'description'] as $field) {
            if (! array_key_exists($field, $fields)) {
                continue;
            }

            $value = trim((string) ($fields[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            self::validateContent($value, $field);
        }
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

    public function removals(): HasMany
    {
        return $this->hasMany(ChannelRemoval::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ChannelInvitation::class);
    }

    public function pendingInvitations(): HasMany
    {
        return $this->invitations()->pending();
    }

    public function hasPendingInvite(User $user): bool
    {
        return $this->invitations()
            ->where('user_id', $user->id)
            ->pending()
            ->exists();
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
        if ($user->isAdmin()) {
            return $query->active();
        }

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

    public function isRemoved(User $user): bool
    {
        return $this->removals()->where('user_id', $user->id)->exists();
    }

    public function markRemoved(User $user, ?User $removedBy = null): void
    {
        $this->removals()->updateOrCreate(
            ['user_id' => $user->id],
            ['removed_by' => $removedBy?->id]
        );
    }

    public function clearRemoval(User $user): void
    {
        $this->removals()->where('user_id', $user->id)->delete();
    }

    /**
     * Self-join is allowed for public channels only if the user was not removed.
     * Removed members must wait for an invite.
     */
    public function canSelfJoin(User $user): bool
    {
        if ($this->type !== self::TYPE_PUBLIC || ! $this->is_active) {
            return false;
        }

        return ! $this->isRemoved($user);
    }

    public function addMember(User $user, ?string $role = null): void
    {
        $this->clearRemoval($user);

        if (! $this->isMember($user)) {
            $this->members()->attach($user->id, [
                'role'         => $role ?? self::roleForUser($user),
                'last_read_at' => now(),
            ]);
        }
    }

    public function removeMember(User $user, ?User $removedBy = null): void
    {
        $this->members()->detach($user->id);
        $this->markRemoved($user, $removedBy);
    }

    public function memberRole(User $user): ?string
    {
        $member = $this->members()->where('channel_members.user_id', $user->id)->first();

        return $member?->pivot?->role;
    }

    public function canAccess(User $user): bool
    {
        if ($user->isAdmin() && $this->is_active) {
            return true;
        }

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

        if ($user->isAdmin()) {
            return true;
        }

        if ($this->isMember($user)) {
            return true;
        }

        // Public channels: allow post/join only if not previously removed
        if ($this->type === self::TYPE_PUBLIC) {
            return $this->canSelfJoin($user);
        }

        return false;
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
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'icon'           => $this->icon,
            'type'           => $this->type,
            'category'       => $this->category,
            'image_path'     => $this->image_url,
            'image_url'      => $this->image_url,
            'video_path'     => $this->video_url,
            'video_url'      => $this->video_url,
            'is_active'      => $this->is_active,
            'created_by'     => $this->created_by,
            'creator'        => $this->relationLoaded('creator')
                ? $this->creator?->only(['id', 'name', 'avatar_url', 'role'])
                : null,
            'messages_count' => $this->all_messages_count ?? $this->allMessages()->count(),
            'members_count'  => $this->members_count ?? $this->members()->count(),
            'created_at'     => $this->created_at,
        ];

        if ($user) {
            $data['is_member']       = $this->isMember($user);
            $data['member_role']     = $this->memberRole($user);
            $data['is_removed']      = $this->isRemoved($user);
            $data['can_self_join']   = $this->canSelfJoin($user);
            $data['has_pending_invite'] = $this->hasPendingInvite($user);
            $data['unread_count']    = $this->isMember($user) ? $this->unreadCountFor($user) : 0;
            $data['is_creator']      = (int) $this->created_by === (int) $user->id;
        }

        return $data;
    }
}
