<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'html_content',
        'show_in_menu',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'show_in_menu' => 'boolean',
        'is_published' => 'boolean',
    ];
}
