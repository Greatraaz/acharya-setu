<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'channel_id', 'user_id', 'body', 'image_path', 'parent_id',
        'likes_count', 'liked_by',
    ];

    protected $casts = [
        'liked_by'    => 'array',
        'likes_count' => 'integer',
    ];

    protected $appends = ['image_url'];

    protected static function booted(): void
    {
        static::deleting(function (self $message) {
            if ($message->image_path && Storage::disk('public')->exists($message->image_path)) {
                Storage::disk('public')->delete($message->image_path);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->with('user')->latest();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function isLikedBy(int $userId): bool
    {
        return in_array($userId, $this->liked_by ?? [], true);
    }

    public function toggleLike(int $userId): self
    {
        $liked = collect($this->liked_by ?? []);

        if ($liked->contains($userId)) {
            $liked = $liked->reject(fn ($id) => (int) $id === $userId)->values();
            $this->likes_count = max(0, (int) $this->likes_count - 1);
        } else {
            $liked->push($userId);
            $this->likes_count = (int) $this->likes_count + 1;
        }

        $this->liked_by = $liked->map(fn ($id) => (int) $id)->unique()->values()->all();
        $this->save();

        return $this;
    }

    public function toApiArray(?int $viewerId = null): array
    {
        return [
            'id'           => $this->id,
            'channel_id'   => $this->channel_id,
            'user_id'      => $this->user_id,
            'body'         => $this->body,
            'message'      => $this->body,
            'image_path'   => $this->image_path,
            'image_url'    => $this->image_url,
            'parent_id'    => $this->parent_id,
            'likes'        => (int) $this->likes_count,
            'likes_count'  => (int) $this->likes_count,
            'liked_by'     => $this->liked_by ?? [],
            'is_liked'     => $viewerId ? $this->isLikedBy($viewerId) : false,
            'replies_count'=> $this->replies_count ?? $this->replies()->count(),
            'user'         => $this->relationLoaded('user') ? $this->user?->only(['id', 'name', 'avatar_url', 'role']) : null,
            'replies'      => $this->relationLoaded('replies')
                ? $this->replies->map(fn (self $r) => $r->toApiArray($viewerId))->values()
                : [],
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
