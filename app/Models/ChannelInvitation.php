<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelInvitation extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'channel_id',
        'user_id',
        'invited_by',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'channel_id'  => $this->channel_id,
            'channel'     => $this->relationLoaded('channel')
                ? [
                    'id'          => $this->channel->id,
                    'name'        => $this->channel->name,
                    'slug'        => $this->channel->slug,
                    'icon'        => $this->channel->icon,
                    'type'        => $this->channel->type,
                    'description' => $this->channel->description,
                ]
                : null,
            'invited_by'  => $this->relationLoaded('invitedBy')
                ? $this->invitedBy?->only(['id', 'name', 'avatar_url', 'role'])
                : null,
            'user'        => $this->relationLoaded('user')
                ? $this->user?->only(['id', 'name', 'avatar_url', 'role'])
                : null,
            'responded_at'=> $this->responded_at,
            'created_at'  => $this->created_at,
        ];
    }
}
