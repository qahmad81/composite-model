<?php

namespace App\Services\Ai;

class AiResponse
{
    public function __construct(
        public string $content,
        public int $inputTokens,
        public int $outputTokens,
        public array $usage,
        public string $cost,
        public ?string $finishReason = null,
        public array $metadata = []
    ) {}
}
