<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTransaction extends Model
{
    protected $fillable = [
        'client_id',
        'internal_token_id',
        'pending_reservation_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function internalToken(): BelongsTo
    {
        return $this->belongsTo(InternalToken::class);
    }
}