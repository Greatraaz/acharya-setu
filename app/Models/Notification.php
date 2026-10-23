<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markRead(): void
    {
        if ($this->is_read) {
            return;
        }

        $this->forceFill([
            'is_read' => true,
            'read_at' => now(),
        ])->save();
    }

    public function toFeedArray(): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'title'            => $this->title,
            'body'             => $this->body,
            'data'             => $this->data ?: new \stdClass(),
            'url'              => $this->url,
            'is_read'          => (bool) $this->is_read,
            'created_at'       => optional($this->created_at)?->toIso8601String(),
            'created_at_human' => optional($this->created_at)?->diffForHumans(),
        ];
    }
}
