<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getSetting(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
