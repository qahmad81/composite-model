<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderModel extends Model
{
    protected $fillable = [
        'provider_id',
        'name',
        'model_key',
        'role',
        'context_window',
        'is_enabled',
        'is_default',
        'metadata',
        'pricing',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array',
        'pricing' => 'array',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function costForUsage(array $usage): string
    {
        $pricing = $this->pricing ?? [];
        
        $inputTokens = $usage['prompt_tokens'] ?? 0;
        $outputTokens = $usage['completion_tokens'] ?? 0;
        $cacheHitTokens = $usage['cache_hit_tokens'] ?? $usage['prompt_cache_hit_tokens'] ?? 0;
        $reasoningTokens = $usage['reasoning_tokens'] ?? $usage['completion_details']['reasoning_tokens'] ?? 0;

        $inputPrice = $pricing['input_tokens'] ?? 0; // per 1M
        $outputPrice = $pricing['output_tokens'] ?? 0; // per 1M
        $cachePrice = $pricing['cache_tokens'] ?? $pricing['prompt_cache_hit_tokens'] ?? 0; // per 1M
        $reasoningPrice = $pricing['reasoning_tokens'] ?? 0; // per 1M

        $total = ($inputTokens * $inputPrice / 1000000) +
                 ($outputTokens * $outputPrice / 1000000) +
                 ($cacheHitTokens * $cachePrice / 1000000) +
                 ($reasoningTokens * $reasoningPrice / 1000000);

        return number_format($total, 6);
    }
}
