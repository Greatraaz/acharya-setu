<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Message extends Model
{
    use SoftDeletes;

    public const IMAGE_MAX_KB = 5120;

    public const IMAGE_MIMES = 'jpeg,jpg,png,webp,gif';

    protected $fillable = [
        'channel_id', 'user_id', 'body', 'image_path', 'video_path', 'parent_id',
        'likes_count', 'liked_by',
    ];

    protected $casts = [
        'liked_by'    => 'array',
        'likes_count' => 'integer',
    ];

    protected $appends = ['image_url', 'video_url', 'youtube_embed_url'];

    protected static function booted(): void
    {
        static::deleting(function (self $message) {
            $message->deleteStoredMedia();
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
     * Shared validation rules for web + API message posts.
     */
    public static function mediaValidationRules(): array
    {
        return [
            'body'         => 'nullable|string|max:5000',
            'message'      => 'nullable|string|max:5000',
            'parent_id'    => 'nullable|exists:messages,id',
            'image'        => 'nullable|image|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB,
            'youtube_url'  => 'nullable|string|max:500',
            'video_url'    => 'nullable|string|max:500',
        ];
    }

    /**
     * Resolve YouTube URL from request input (supports youtube_url and video_url aliases).
     */
    public static function youtubeUrlFromInput(array $input): ?string
    {
        foreach (['youtube_url', 'video_url'] as $key) {
            $value = trim((string) ($input[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Normalize a YouTube link to a canonical watch URL, or null if invalid.
     */
    public static function normalizeYoutubeUrl(?string $url): ?string
    {
        $id = self::extractYoutubeVideoId($url);

        return $id ? 'https://www.youtube.com/watch?v='.$id : null;
    }

    /**
     * Extract a YouTube video id from common watch, share, embed, and shorts URLs.
     */
    public static function extractYoutubeVideoId(?string $url): ?string
    {
        if (! $url || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('~(?:youtube\.com/watch\?(?:[^&]*&)*v=|youtube\.com/embed/|youtube\.com/shorts/|youtube\.com/live/|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Validate and normalize a YouTube URL for storage in video_path.
     *
     * @throws ValidationException
     */
    public static function resolveStoredYoutubeUrl(?string $url, string $field = 'youtube_url'): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $normalized = self::normalizeYoutubeUrl($url);

        if (! $normalized) {
            throw ValidationException::withMessages([
                $field => ['Enter a valid YouTube URL (youtube.com or youtu.be).'],
            ]);
        }

        return $normalized;
    }

    public function hasYoutubeVideo(): bool
    {
        return self::extractYoutubeVideoId($this->video_path) !== null;
    }

    /**
     * Absolute public URL for the message image.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->image_path);
    }

    /**
     * Stored YouTube watch URL or legacy uploaded video file URL.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (! $this->video_path) {
            return null;
        }

        if ($this->hasYoutubeVideo()) {
            return self::normalizeYoutubeUrl($this->video_path);
        }

        return $this->publicMediaUrl($this->video_path);
    }

    /**
     * YouTube embed iframe src for stored links.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $id = self::extractYoutubeVideoId($this->video_path);

        return $id ? 'https://www.youtube.com/embed/'.$id : null;
    }

    public function deleteStoredMedia(): void
    {
        $this->deleteStoredPath($this->image_path);

        if ($this->video_path && ! $this->hasYoutubeVideo()) {
            $this->deleteStoredPath($this->video_path);
        }
    }

    /** @deprecated use deleteStoredMedia */
    public function deleteStoredImage(): void
    {
        $this->deleteStoredPath($this->image_path);
    }

    /**
     * Store an uploaded community image under public/upload/community/{channelId}.
     */
    public static function storeUploadedImage(UploadedFile $file, int $channelId): string
    {
        return self::storeUploadedMedia($file, $channelId, 'img');
    }

    public static function storeUploadedMedia(UploadedFile $file, int $channelId, string $prefix = 'msg'): string
    {
        $directory = public_path('upload/community/'.$channelId);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $fileName = $prefix.'_'.time().'_'.uniqid().'.'.$extension;
        $file->move($directory, $fileName);

        return 'upload/community/'.$channelId.'/'.$fileName;
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
        $videoUrl = $this->video_url;
        $youtubeEmbedUrl = $this->youtube_embed_url;

        return [
            'id'                => $this->id,
            'channel_id'        => $this->channel_id,
            'user_id'           => $this->user_id,
            'body'              => $this->body,
            'message'           => $this->body,
            'image_path'        => $imageUrl,
            'image_url'         => $imageUrl,
            'video_path'        => $videoUrl,
            'video_url'         => $videoUrl,
            'youtube_url'       => $this->hasYoutubeVideo() ? $videoUrl : null,
            'youtube_embed_url' => $youtubeEmbedUrl,
            'parent_id'         => $this->parent_id,
            'likes'             => (int) $this->likes_count,
            'likes_count'       => (int) $this->likes_count,
            'liked_by'          => $this->liked_by ?? [],
            'is_liked'          => $viewerId ? $this->isLikedBy($viewerId) : false,
            'replies_count'     => $this->replies_count ?? $this->replies()->count(),
            'user'              => $this->relationLoaded('user') ? $this->user?->only(['id', 'name', 'avatar_url', 'role']) : null,
            'replies'           => $this->relationLoaded('replies')
                ? $this->replies->map(fn (self $r) => $r->toApiArray($viewerId))->values()
                : [],
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
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

        if (str_starts_with($path, '/storage/')) {
            return url(ltrim($path, '/'));
        }

        return url('storage/'.ltrim($path, '/'));
    }

    private function deleteStoredPath(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
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
}
