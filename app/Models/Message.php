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
            $message->deleteStoredImage();
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

    /**
     * Absolute public URL for the message image (same style as avatar uploads).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $path = $this->image_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // New uploads: public/upload/community/...
        if (str_starts_with($path, 'upload/')) {
            return url($path);
        }

        // Legacy Storage::disk('public') paths: community/{id}/file.jpg
        if (str_starts_with($path, '/storage/')) {
            return url(ltrim($path, '/'));
        }

        return url('storage/' . ltrim($path, '/'));
    }

    public function deleteStoredImage(): void
    {
        if (! $this->image_path) {
            return;
        }

        $path = $this->image_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?: '';
            $path = ltrim((string) $path, '/');
        }

        if (str_starts_with($path, 'upload/')) {
            $absolute = public_path($path);
            if (is_file($absolute)) {
                @unlink($absolute);
            }

            return;
        }

        $storagePath = str_starts_with($path, 'storage/')
            ? substr($path, strlen('storage/'))
            : ltrim($path, '/');

        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }

    /**
     * Store an uploaded community image under public/upload/community/{channelId}.
     * Returns relative path like upload/community/5/xxx.jpg (converted to full URL via image_url).
     */
    public static function storeUploadedImage($file, int $channelId): string
    {
        $directory = public_path('upload/community/' . $channelId);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $fileName = 'msg_' . time() . '_' . uniqid() . '.' . $extension;
        $file->move($directory, $fileName);

        return 'upload/community/' . $channelId . '/' . $fileName;
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
        $imageUrl = $this->image_url;

        return [
            'id'           => $this->id,
            'channel_id'   => $this->channel_id,
            'user_id'      => $this->user_id,
            'body'         => $this->body,
            'message'      => $this->body,
            'image_path'   => $imageUrl, // full absolute URL for clients
            'image_url'    => $imageUrl,
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
