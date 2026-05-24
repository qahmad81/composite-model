<?php

namespace App\Services\Ai;

use App\Models\Provider;
use InvalidArgumentException;

class ProviderClientManager
{
    public function resolve(Provider $provider): ProviderClient
    {
        return match ($provider->driver) {
            'openai', 'ollama', 'lmstudio', 'vllm' => app(OpenAiProviderClient::class),
            // Anthropic and Google could be added here if they have dedicated clients, 
            // but for now many use OpenAI compatible APIs or we stick to the OpenAI client for this step.
            default => throw new InvalidArgumentException("Unsupported driver: {$provider->driver}"),
        };
    }
}
