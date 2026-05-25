<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;

class StartNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        // Start node just passes the incoming messages as output
        $lastMessage = end($context->messages);
        $output = $lastMessage['content'] ?? '';
        
        return new NodeResult(
            $output,
            0, 0, 0,
            ['status' => 'started']
        );
    }
}
