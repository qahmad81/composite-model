<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompositeModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'flow_json',
        'top_credit_limit',
        'is_enabled',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'flow_json' => 'array',
        'settings' => 'array',
        'is_enabled' => 'boolean',
    ];
}
