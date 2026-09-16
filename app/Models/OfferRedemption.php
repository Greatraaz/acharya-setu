<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferRedemption extends Model
{
    public const TYPE_WALLET_CREDIT = 'wallet_credit';

    public const TYPE_SESSION_DISCOUNT = 'session_discount';

    protected $fillable = [
        'offer_id',
        'user_id',
        'consultation_session_id',
        'amount',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class, 'consultation_session_id');
    }
}
