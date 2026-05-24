<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompositeModule;
use App\Services\Flow\FlowEngine;
use App\Services\PricingService;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatCompletionsController extends Controller
{
    public function __construct(
        protected FlowEngine $flowEngine,
        protected TokenService $tokenService,
        protected PricingService $pricingService
    ) {}

    public function store(Request $request)
    {
        $modelSlug = $request->input('model');
        $messages = $request->input('messages');
        $stream = $request->input('stream', true);

        $module = CompositeModule::where('slug', $modelSlug)
            ->where('is_enabled', true)
            ->first();

        if (!$module) {
            return response()->json([
                'error' => [
                    'message' => "The model '$modelSlug' does not exist.",
                    'type' => 'invalid_request_error',
                    'param' => 'model',
                    'code' => 'model_not_found'
                ]
            ], 404);
        }

        $token = $request->attributes->get('internal_token');
        $clientBalance = \App\Models\ClientBalance::where('client_id', $token->client_id)->first();

        if (!$clientBalance || $clientBalance->pending_balance < $module->top_credit_limit) {
            return response()->json([
                'error' => [
                    'message' => 'Insufficient balance.',
                    'type' => 'insufficient_balance_error',
                    'param' => null,
                    'code' => 'insufficient_balance'
                ]
            ], 402);
        }

        $reservationId = $this->tokenService->reserve($token, $module->top_credit_limit, "Chat Completion: $modelSlug");

        try {
            $result = $this->flowEngine->execute($module, $messages, $token, $reservationId);

            if ($result->error) {
                $this->tokenService->cancel($reservationId);
                return response()->json([
                    'error' => [
                        'message' => $result->error,
                        'type' => 'api_error',
                        'param' => null,
                        'code' => null
                    ]
                ], 500);
            }

            $actualCost = $this->pricingService->calculateCost($result->cost, 'internal');
            $this->tokenService->confirm($reservationId, $actualCost);

            $id = 'chatcmpl-' . Str::random(24);
            $created = time();

            if ($stream) {
                return $this->streamResponse($id, $created, $modelSlug, $result->output);
            }

            return response()->json([
                'id' => $id,
                'object' => 'chat.completion',
                'created' => $created,
                'model' => $modelSlug,
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => $result->output,
                        ],
                        'finish_reason' => 'stop',
                    ]
                ],
                'usage' => [
                    'prompt_tokens' => $result->tokens_input,
                    'completion_tokens' => $result->tokens_output,
                    'total_tokens' => $result->tokens_input + $result->tokens_output,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Chat completion failed: " . $e->getMessage());
            try {
                $this->tokenService->cancel($reservationId);
            } catch (\Exception $e2) {
                Log::error("Failed to cancel reservation after error: " . $e2->getMessage());
            }
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => 'api_error',
                    'param' => null,
                    'code' => null
                ]
            ], 500);
        }
    }

    protected function streamResponse(string $id, int $created, string $model, string $content): StreamedResponse
    {
        return new StreamedResponse(function () use ($id, $created, $model, $content) {
            // Simulated streaming by breaking content into small chunks
            // In a real scenario, the FlowEngine would ideally yield chunks
            $words = preg_split('/(\s+)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            foreach ($words as $word) {
                if ($word === '') continue;
                
                $data = [
                    'id' => $id,
                    'object' => 'chat.completion.chunk',
                    'created' => $created,
                    'model' => $model,
                    'choices' => [
                        [
                            'index' => 0,
                            'delta' => [
                                'content' => $word,
                            ],
                            'finish_reason' => null,
                        ]
                    ]
                ];

                echo "data: " . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                usleep(20000); // 20ms delay per word/chunk
            }

            $finalData = [
                'id' => $id,
                'object' => 'chat.completion.chunk',
                'created' => $created,
                'model' => $model,
                'choices' => [
                    [
                        'index' => 0,
                        'delta' => [],
                        'finish_reason' => 'stop',
                    ]
                ]
            ];
            echo "data: " . json_encode($finalData) . "\n\n";
            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
