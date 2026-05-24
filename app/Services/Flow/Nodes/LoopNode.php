<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;
use Illuminate\Support\Facades\Log;
use Exception;

class LoopNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $maxIterations = $this->config['max_iterations'] ?? 5;
        $loopBodyNodeIds = $this->config['loop_body_node_ids'] ?? [];
        $breakCondition = $this->config['break_condition'] ?? 'false';
        
        $totalTokensInput = 0;
        $totalTokensOutput = 0;
        $totalCost = 0;
        $lastOutput = '';

        if (empty($loopBodyNodeIds)) {
            return new NodeResult('Empty loop body', 0, 0, 0);
        }

        for ($i = 0; $i < $maxIterations; $i++) {
            $context->variables['loop_iteration'] = $i;
            
            // Execute body nodes
            // We need a sub-engine or similar logic here. 
            // For simplicity, we assume the nodes are executed in order.
            foreach ($loopBodyNodeIds as $nodeId) {
                $nodeData = collect($context->composite_module->flow_json['nodes'])->firstWhere('id', $nodeId);
                if (!$nodeData) continue;

                $node = $this->makeNode($nodeData);
                $result = $node->execute($context);

                if ($result->error) {
                    return $result;
                }

                $totalTokensInput += $result->tokens_input;
                $totalTokensOutput += $result->tokens_output;
                $totalCost += $result->cost;
                $lastOutput = $result->output;

                // Update context variables
                if (isset($result->metadata['new_variables'])) {
                    $context->variables = array_merge($context->variables, $result->metadata['new_variables']);
                }
            }

            // Evaluate break condition
            if ($this->evaluateCondition($breakCondition, $context)) {
                break;
            }
        }

        return new NodeResult(
            $lastOutput,
            $totalTokensInput,
            $totalTokensOutput,
            $totalCost,
            ['iterations' => $i + 1]
        );
    }

    protected function evaluateCondition(string $expression, FlowContext $context): bool
    {
        $variablesJson = json_encode((object)$context->variables);
        $code = "const variables = $variablesJson; try { process.stdout.write(eval(\"$expression\") ? '1' : '0'); } catch(e) { process.stdout.write('0'); }";
        $output = [];
        $returnVar = 0;
        exec("node -e " . escapeshellarg($code), $output, $returnVar);
        return trim(implode('', $output)) === '1';
    }

    protected function makeNode(array $data)
    {
        $id = $data['id'];
        $type = $data['type'];
        $config = $data['config'] ?? [];

        return match ($type) {
            'model' => new ModelNode($id, $type, $config),
            'condition' => new ConditionNode($id, $type, $config),
            'router' => new RouterNode($id, $type, $config),
            'data_processor' => new DataProcessorNode($id, $type, $config),
            'parallel' => new ParallelNode($id, $type, $config),
            'aggregator' => new AggregatorNode($id, $type, $config),
            'loop' => new LoopNode($id, $type, $config),
            default => throw new Exception("Unknown node type: $type"),
        };
    }
}
