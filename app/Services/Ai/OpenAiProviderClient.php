<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAiProviderClient implements ProviderClient
{
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse
    {
        $apiKey = $this->resolveApiKey($provider);
        
        $response = Http::withToken($apiKey)
            ->timeout($options['timeout'] ?? 60)
            ->post(rtrim($provider->base_url, '/') . '/chat/completions', [
                'model' => $model->model_key,
                'messages' => $messages,
                'stream' => $options['stream'] ?? false,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? null,
            ]);

        if ($response->failed()) {
            throw new Exception("AI Provider Error: " . ($response->json('error.message') ?? $response->body()));
        }

        $data = $response->json();
        $usage = $this->normalizeUsage($data['usage'] ?? []);
        
        return new AiResponse(
            content: $data['choices'][0]['message']['content'] ?? '',
            inputTokens: $usage['prompt_tokens'] ?? 0,
            outputTokens: $usage['completion_tokens'] ?? 0,
            usage: $usage,
            cost: $model->costForUsage($usage),
            finishReason: $data['choices'][0]['finish_reason'] ?? null,
            metadata: $data
        );
    }

    protected function resolveApiKey(Provider $provider): string
    {
        if ($provider->api_key_env && env($provider->api_key_env)) {
            return env($provider->api_key_env);
        }
        
        return $provider->metadata['api_key'] ?? '';
    }

    protected function normalizeUsage(array $usage): array
    {
        // Handle common OpenAI usage expansions
        if (isset($usage['prompt_tokens_details']['cached_tokens'])) {
            $usage['prompt_cache_hit_tokens'] = $usage['prompt_tokens_details']['cached_tokens'];
        }

        if (isset($usage['completion_tokens_details']['reasoning_tokens'])) {
            $usage['reasoning_tokens'] = $usage['completion_tokens_details']['reasoning_tokens'];
        }

        return $usage;
    }
}
