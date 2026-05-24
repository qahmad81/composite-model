<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientBalance extends Model
{
    protected $fillable = [
        'client_id',
        'final_balance',
        'pending_balance',
    ];

    protected $casts = [
        'final_balance' => 'integer',
        'pending_balance' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}