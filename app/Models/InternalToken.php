<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'token_hash',
        'name',
        'is_active',
        'rate_limit',
        'limit_balance',
        'final_balance',
        'pending_balance',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit' => 'integer',
        'limit_balance' => 'integer',
        'final_balance' => 'integer',
        'pending_balance' => 'integer',
        'settings' => 'json',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(PendingReservation::class);
    }
}