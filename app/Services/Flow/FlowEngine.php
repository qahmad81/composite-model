<?php

namespace App\Services\Flow;

use App\Models\CompositeModule;
use App\Models\InternalToken;
use App\Services\TokenService;
use App\Services\Flow\Nodes\ModelNode;
use App\Services\Flow\Nodes\ConditionNode;
use App\Services\Flow\Nodes\RouterNode;
use App\Services\Flow\Nodes\DataProcessorNode;
use App\Services\Flow\Nodes\ParallelNode;
use App\Services\Flow\Nodes\AggregatorNode;
use App\Services\Flow\Nodes\LoopNode;
use Exception;
use Illuminate\Support\Str;

class FlowEngine
{
    public function __construct(
        protected TokenService $tokenService
    ) {}

    public function execute(CompositeModule $module, array $messages, $token, ?int $reservationId = null): NodeResult
    {
        $flow = $module->flow_json;
        $nodes = $flow['nodes'] ?? [];
        $edges = $flow['edges'] ?? [];
        $startNodeId = $flow['startNodeId'] ?? ($nodes[0]['id'] ?? null);

        if (!$startNodeId) {
            throw new Exception("Start node not found in flow.");
        }

        $context = new FlowContext(
            request_id: (string)Str::uuid(),
            messages: $messages,
            reservation_id: $reservationId,
            token: $token,
            composite_module: $module,
            variables: []
        );

        $currentNodeId = $startNodeId;
        $totalTokensInput = 0;
        $totalTokensOutput = 0;
        $totalCost = 0;
        $lastOutput = '';

        while ($currentNodeId) {
            $nodeData = collect($nodes)->firstWhere('id', $currentNodeId);
            if (!$nodeData) {
                break;
            }

            $node = $this->makeNode($nodeData);
            $result = $node->execute($context);

            if ($result->error) {
                return $result;
            }

            $totalTokensInput += $result->tokens_input;
            $totalTokensOutput += $result->tokens_output;
            $totalCost += $result->cost;
            $lastOutput = $result->output;

            $metadata = $result->metadata;

            // Update context
            if ($result->output !== 'processed') {
                $context->accumulated_output .= ($context->accumulated_output ? "\n" : "") . $result->output;
            }
            
            if (isset($metadata['new_variables'])) {
                $context->variables = array_merge($context->variables, $metadata['new_variables']);
                unset($metadata['new_variables']);
            }

            $context->node_metadata = array_merge($context->node_metadata, $metadata);

            // Sync reservation if needed
            if ($context->reservation_id && $totalCost > 0) {
                 // For simplicity, we just update the reservation to the current total cost
                 // In a real system, we might want to reserve more than needed upfront.
                 $this->tokenService->modifyReservation($context->reservation_id, $totalCost);
            }

            // Determine next node
            $nextNodeId = $result->metadata['next_node_id'] ?? null;
            if (!$nextNodeId) {
                $edge = collect($edges)->firstWhere('source', $currentNodeId);
                $nextNodeId = $edge['target'] ?? null;
            }

            $currentNodeId = $nextNodeId;
        }

        return new NodeResult(
            output: $lastOutput,
            tokens_input: $totalTokensInput,
            tokens_output: $totalTokensOutput,
            cost: $totalCost,
            metadata: array_merge(['variables' => $context->variables], $context->node_metadata)
        );
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
