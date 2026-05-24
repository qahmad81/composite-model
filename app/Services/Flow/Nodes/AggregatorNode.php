<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;
use Exception;

class AggregatorNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $strategy = $this->config['aggregation_strategy'] ?? 'concatenate';
        $primaryPaths = $this->config['primary_paths'] ?? []; // branch_ids that must be present
        $secondaryPaths = $this->config['secondary_paths'] ?? []; // branch_ids that are optional
        $template = $this->config['custom_template'] ?? '';

        $outputs = [];
        
        // Check primary paths
        foreach ($primaryPaths as $path) {
            if (!isset($context->variables[$path]) || str_starts_with($context->variables[$path], 'Error:')) {
                return new NodeResult('', 0, 0, 0, [], "Missing or error in primary path: $path");
            }
            $outputs[$path] = $context->variables[$path];
        }

        // Check secondary paths
        foreach ($secondaryPaths as $path) {
            if (isset($context->variables[$path]) && !str_starts_with($context->variables[$path], 'Error:')) {
                $outputs[$path] = $context->variables[$path];
            }
        }

        $aggregatedOutput = '';

        switch ($strategy) {
            case 'json_merge':
                $merged = [];
                foreach ($outputs as $val) {
                    $decoded = json_decode($val, true);
                    if (is_array($decoded)) {
                        $merged = array_merge($merged, $decoded);
                    }
                }
                $aggregatedOutput = json_encode($merged);
                break;

            case 'custom_template':
                $aggregatedOutput = $template;
                foreach ($outputs as $id => $val) {
                    $aggregatedOutput = str_replace("{{$id}}", $val, $aggregatedOutput);
                }
                break;

            case 'concatenate':
            default:
                $aggregatedOutput = implode("\n---\n", array_values($outputs));
                break;
        }

        return new NodeResult($aggregatedOutput, 0, 0, 0);
    }
}
