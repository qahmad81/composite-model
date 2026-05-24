<?php

namespace App\Services\Flow;

class NodeResult
{
    public function __construct(
        public string $output,
        public int $tokens_input = 0,
        public int $tokens_output = 0,
        public int $cost = 0,
        public array $metadata = [],
        public ?string $error = null
    ) {}
}
