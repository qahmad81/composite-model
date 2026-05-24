<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function balance(): HasOne
    {
        return $this->hasOne(ClientBalance::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(InternalToken::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InternalTransaction::class);
    }
}