<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;

use Illuminate\Support\Facades\Log;

class ConditionNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $expression = $this->config['expression'] ?? 'true';
        $variablesJson = json_encode((object)$context->variables);
        
        $code = "const variables = $variablesJson; try { process.stdout.write(eval(\"$expression\") ? '1' : '0'); } catch(e) { process.stdout.write('0'); }";
        $output = [];
        $returnVar = 0;
        
        Log::info("Condition Node execution code: " . $code);
        exec("node -e " . escapeshellarg($code), $output, $returnVar);
        Log::info("Condition Node output: " . implode('', $output));
        
        $result = trim(implode('', $output)) === '1';
        $nextNodeId = $result ? ($this->config['true_branch'] ?? null) : ($this->config['false_branch'] ?? null);

        return new NodeResult(
            $result ? 'true' : 'false',
            0, 0, 0,
            ['next_node_id' => $nextNodeId]
        );
    }
}
