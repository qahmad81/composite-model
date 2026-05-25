<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;
use App\Services\Ai\ProviderClientManager;
use App\Models\ProviderModel;
use App\Models\ExecutionLog;
use Exception;

class ModelNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $modelId = $this->config['provider_model_id'] ?? $this->config['model_id'] ?? null;
        $fallbackModelId = $this->config['fallback_model_id'] ?? null;
        $systemPrompt = $this->config['system_prompt'] ?? '';

        try {
            return $this->callModel($modelId, $context, $systemPrompt);
        } catch (Exception $e) {
            if ($fallbackModelId) {
                try {
                    return $this->callModel($fallbackModelId, $context, $systemPrompt);
                } catch (Exception $fallbackEx) {
                    return new NodeResult('', 0, 0, 0, [], $fallbackEx->getMessage());
                }
            }
            return new NodeResult('', 0, 0, 0, [], $e->getMessage());
        }
    }

    protected function callModel(int $modelId, FlowContext $context, string $systemPrompt): NodeResult
    {
        $startTime = microtime(true);
        $model = ProviderModel::with('provider')->findOrFail($modelId);
        $manager = app(ProviderClientManager::class);
        $client = $manager->resolve($model->provider);

        $messages = $context->messages;
        if ($systemPrompt) {
            array_unshift($messages, ['role' => 'system', 'content' => $systemPrompt]);
        }

        // Add accumulated output if any as a hint or part of the context
        if ($context->accumulated_output) {
            $messages[] = ['role' => 'assistant', 'content' => "Previous context: " . $context->accumulated_output];
        }

        $response = $client->chat($model->provider, $model, $messages);

        $duration = (int)((microtime(true) - $startTime) * 1000);

        $log = ExecutionLog::create([
            'composite_module_id' => $context->composite_module->id,
            'token_id' => $context->token->id,
            'token_type' => get_class($context->token),
            'request_id' => $context->request_id,
            'node_id' => $this->id,
            'node_type' => $this->type,
            'input' => ['messages' => $messages],
            'output' => ['content' => $response->content],
            'tokens_used_input' => $response->inputTokens,
            'tokens_used_output' => $response->outputTokens,
            'cost' => (int)($response->cost * 1000000), // Convert to BIGINT micro-units
            'duration_ms' => $duration,
            'status' => 'success',
        ]);

        return new NodeResult(
            $response->content,
            $response->inputTokens,
            $response->outputTokens,
            (int)($response->cost * 1000000),
            ['log_id' => $log->id]
        );
    }
}
