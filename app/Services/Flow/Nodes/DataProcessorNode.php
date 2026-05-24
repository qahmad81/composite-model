<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;

class DataProcessorNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $script = $this->config['script'] ?? '';
        $variablesJson = json_encode((object)$context->variables);
        
        $code = "let variables = $variablesJson; try { $script; console.log(JSON.stringify(variables)); } catch(e) { console.log(JSON.stringify({error: e.message})); }";
        $output = [];
        $returnVar = 0;
        
        exec("node -e " . escapeshellarg($code), $output, $returnVar);
        
        $resultJson = implode('', $output);
        $newVariables = json_decode($resultJson, true) ?? [];
        
        if (isset($newVariables['error'])) {
             return new NodeResult('', 0, 0, 0, [], "JS Error: " . $newVariables['error']);
        }

        // Merge back
        reset($newVariables);
        return new NodeResult(
            'processed',
            0, 0, 0,
            ['new_variables' => $newVariables]
        );
    }
}
