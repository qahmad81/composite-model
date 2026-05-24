<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;

abstract class BaseNode
{
    public function __construct(
        public string $id,
        public string $type,
        public array $config
    ) {}

    abstract public function execute(FlowContext $context): NodeResult;
}
