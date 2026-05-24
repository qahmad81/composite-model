<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'composite_module_id',
        'token_id',
        'token_type',
        'request_id',
        'node_id',
        'node_type',
        'input',
        'output',
        'tokens_used_input',
        'tokens_used_output',
        'cost',
        'duration_ms',
        'error',
        'status',
    ];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
    ];

    public function token()
    {
        return $this->morphTo();
    }

    public function compositeModule()
    {
        return $this->belongsTo(CompositeModule::class);
    }
}
