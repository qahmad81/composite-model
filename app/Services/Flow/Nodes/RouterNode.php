<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;

class RouterNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $field = $this->config['field'] ?? '';
        $value = $context->variables[$field] ?? null;
        
        $branches = $this->config['branches'] ?? [];
        $nextNodeId = $branches[$value] ?? ($this->config['default_branch'] ?? null);

        return new NodeResult(
            (string)$value,
            0, 0, 0,
            ['next_node_id' => $nextNodeId]
        );
    }
}
