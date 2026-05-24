<?php

namespace App\Services\Flow;

use App\Models\CompositeModule;

class FlowContext
{
    public function __construct(
        public string $request_id,
        public array $messages,
        public string $accumulated_output = '',
        public ?int $reservation_id = null,
        public $token = null,
        public ?CompositeModule $composite_module = null,
        public array $variables = [],
        public array $node_metadata = []
    ) {}
}
