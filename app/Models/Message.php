<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Message extends Model
{
    use SoftDeletes;

    public const IMAGE_MAX_KB = 5120;

    public const IMAGE_MIMES = 'jpeg,jpg,png,webp,gif';

    public const VIDEO_MAX_KB = 10240;

    public const VIDEO_MIMES = 'mp4,mov,avi,webm,mpeg';

    protected $fillable = [
        'channel_id', 'user_id', 'body', 'image_path', 'video_path', 'parent_id',
        'likes_count', 'liked_by',
    ];

    protected $casts = [
        'liked_by'    => 'array',
        'likes_count' => 'integer',
    ];

    protected $appends = ['image_url', 'video_url'];

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

    public function reports(): HasMany
    {
        return $this->hasMany(MessageReport::class);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('reports', fn (Builder $q) => $q->where('user_id', $user->id));
    }

    public function isReportedBy(int $userId): bool
    {
        return $this->reports()->where('user_id', $userId)->exists();
    }

    /**
     * Shared validation rules for web + API message posts.
     */
    public static function mediaValidationRules(): array
    {
        return [
            'body'      => 'nullable|string|max:5000',
            'message'   => 'nullable|string|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
            'image'     => 'nullable|image|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB,
            'video'     => 'nullable|file|mimes:'.self::VIDEO_MIMES.'|max:'.self::VIDEO_MAX_KB,
        ];
    }

    /**
     * Optional video upload when creating a channel (posted as first message).
     */
    public static function channelVideoValidationRule(): string
    {
        return 'nullable|file|mimes:'.self::VIDEO_MIMES.'|max:'.self::VIDEO_MAX_KB;
    }

    public static function channelImageValidationRule(): string
    {
        return 'nullable|image|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB;
    }

    /**
     * Optional welcome post when creating a channel (image and/or video).
     */
    public static function createOptionalWelcomeMessage(Request $request, Channel $channel, int $userId): ?self
    {
        $hasImage = $request->hasFile('image');
        $hasVideo = $request->hasFile('video');

        if (! $hasImage && ! $hasVideo) {
            return null;
        }

        return self::create([
            'channel_id' => $channel->id,
            'user_id'    => $userId,
            'body'       => '',
            'image_path' => $hasImage ? self::storeUploadedImage($request->file('image'), $channel->id) : null,
            'video_path' => $hasVideo ? self::storeUploadedVideo($request->file('video'), $channel->id) : null,
            'parent_id'  => null,
            'liked_by'   => [],
        ]);
    }

    /**
     * Build message attributes from a post request (text, image, and/or video).
     *
     * @throws ValidationException
     */
    public static function buildPostAttributes(Request $request, Channel $channel, array $data = []): array
    {
        $body = trim((string) ($data['body'] ?? $data['message'] ?? $request->input('body', $request->input('message', ''))));
        $hasImage = $request->hasFile('image');
        $hasVideo = $request->hasFile('video');

        if ($body === '' && ! $hasImage && ! $hasVideo) {
            throw ValidationException::withMessages([
                'body' => ['Message text, image, or video is required.'],
            ]);
        }

        if ($body !== '') {
            Channel::validateMessageBody($body);
        }

        return [
            'body'       => $body,
            'image_path' => $hasImage ? self::storeUploadedImage($request->file('image'), $channel->id) : null,
            'video_path' => $hasVideo ? self::storeUploadedVideo($request->file('video'), $channel->id) : null,
            'parent_id'  => $data['parent_id'] ?? $request->input('parent_id'),
        ];
    }

    /**
     * Absolute public URL for the message image.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->image_path);
    }

    /**
     * Absolute public URL for an uploaded video file.
     */
    public function getVideoUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->video_path);
    }

    public function deleteStoredMedia(): void
    {
        $this->deleteStoredPath($this->image_path);
        $this->deleteStoredPath($this->video_path);
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

    /**
     * Store an uploaded community video under public/upload/community/{channelId}.
     */
    public static function storeUploadedVideo(UploadedFile $file, int $channelId): string
    {
        return self::storeUploadedMedia($file, $channelId, 'vid');
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

        return [
            'id'            => $this->id,
            'channel_id'    => $this->channel_id,
            'user_id'       => $this->user_id,
            'body'          => $this->body,
            'message'       => $this->body,
            'image_path'    => $imageUrl,
            'image_url'     => $imageUrl,
            'video_path'    => $videoUrl,
            'video_url'     => $videoUrl,
            'parent_id'     => $this->parent_id,
            'likes'         => (int) $this->likes_count,
            'likes_count'   => (int) $this->likes_count,
            'liked_by'      => $this->liked_by ?? [],
            'is_liked'      => $viewerId ? $this->isLikedBy($viewerId) : false,
            'is_reported'   => $viewerId ? $this->isReportedBy($viewerId) : false,
            'replies_count' => $this->replies_count ?? $this->replies()->count(),
            'user'          => $this->relationLoaded('user') ? $this->user?->only(['id', 'name', 'avatar_url', 'role']) : null,
            'replies'       => $this->relationLoaded('replies')
                ? $this->replies->map(fn (self $r) => $r->toApiArray($viewerId))->values()
                : [],
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
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
