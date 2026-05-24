<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingReservation extends Model
{
    protected $fillable = [
        'internal_token_id',
        'amount',
        'status',
        'description',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function internalToken(): BelongsTo
    {
        return $this->belongsTo(InternalToken::class);
    }
}