<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\CompositeModule;
use App\Models\InternalToken;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\User;
use App\Services\Ai\AiResponse;
use App\Services\Ai\ProviderClient;
use App\Services\Ai\ProviderClientManager;
use App\Services\Flow\FlowEngine;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FlowEngineAdvancedTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $client;
    protected $provider;
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->client = Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
        app(TokenService::class)->topup($this->client->id, 1000000);

        $rawToken = app(TokenService::class)->generateToken($this->client->id, 'Test Token');
        $this->token = app(TokenService::class)->resolveToken($rawToken);

        $this->provider = Provider::create([
            'name' => 'OpenAI',
            'slug' => 'openai',
            'driver' => 'openai',
            'base_url' => 'https://api.openai.com/v1',
            'api_key_env' => 'OPENAI_API_KEY',
        ]);

        $this->model = ProviderModel::create([
            'provider_id' => $this->provider->id,
            'name' => 'gpt-4o',
            'model_key' => 'gpt-4o',
            'role' => 'chat',
            'context_window' => 128000,
        ]);
    }

    public function test_parallel_execution_collects_all_results()
    {
        $module = CompositeModule::create([
            'name' => 'Parallel Flow',
            'slug' => 'parallel-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'parallel_1',
                        'type' => 'parallel',
                        'config' => [
                            'branches' => [
                                ['branch_id' => 'b1', 'model_id' => $this->model->id],
                                ['branch_id' => 'b2', 'model_id' => $this->model->id]
                            ]
                        ]
                    ]
                ],
                'edges' => [],
                'startNodeId' => 'parallel_1'
            ]
        ]);

        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')->twice()->andReturn(
            new AiResponse('R1', 1, 1, [], '0.000001'),
            new AiResponse('R2', 1, 1, [], '0.000001')
        );

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [], $this->token);

        $this->assertEquals('R1', $result->metadata['variables']['b1']);
        $this->assertEquals('R2', $result->metadata['variables']['b2']);
    }

    public function test_aggregator_combines_outputs()
    {
        $module = CompositeModule::create([
            'name' => 'Aggregator Flow',
            'slug' => 'aggregator-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'parallel_1',
                        'type' => 'parallel',
                        'config' => [
                            'branches' => [
                                ['branch_id' => 'b1', 'model_id' => $this->model->id],
                                ['branch_id' => 'b2', 'model_id' => $this->model->id]
                            ]
                        ]
                    ],
                    [
                        'id' => 'aggregator_1',
                        'type' => 'aggregator',
                        'config' => [
                            'aggregation_strategy' => 'concatenate',
                            'primary_paths' => ['b1', 'b2']
                        ]
                    ]
                ],
                'edges' => [
                    ['source' => 'parallel_1', 'target' => 'aggregator_1']
                ],
                'startNodeId' => 'parallel_1'
            ]
        ]);

        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')->twice()->andReturn(
            new AiResponse('Part 1', 1, 1, [], '0.000001'),
            new AiResponse('Part 2', 1, 1, [], '0.000001')
        );

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [], $this->token);

        $this->assertEquals("Part 1\n---\nPart 2", $result->output);
    }

    public function test_aggregator_custom_template()
    {
        $module = CompositeModule::create([
            'name' => 'Template Flow',
            'slug' => 'template-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'parallel_1',
                        'type' => 'parallel',
                        'config' => [
                            'branches' => [
                                ['branch_id' => 'b1', 'model_id' => $this->model->id],
                                ['branch_id' => 'b2', 'model_id' => $this->model->id]
                            ]
                        ]
                    ],
                    [
                        'id' => 'aggregator_1',
                        'type' => 'aggregator',
                        'config' => [
                            'aggregation_strategy' => 'custom_template',
                            'primary_paths' => ['b1', 'b2'],
                            'custom_template' => 'B1: {b1}, B2: {b2}'
                        ]
                    ]
                ],
                'edges' => [
                    ['source' => 'parallel_1', 'target' => 'aggregator_1']
                ],
                'startNodeId' => 'parallel_1'
            ]
        ]);

        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')->twice()->andReturn(
            new AiResponse('V1', 1, 1, [], '0.000001'),
            new AiResponse('V2', 1, 1, [], '0.000001')
        );

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [], $this->token);

        $this->assertEquals("B1: V1, B2: V2", $result->output);
    }

    public function test_model_node_fallback_on_failure()
    {
        $fallbackModel = ProviderModel::create([
            'provider_id' => $this->provider->id,
            'name' => 'gpt-3.5-turbo',
            'model_key' => 'gpt-3.5-turbo',
            'role' => 'chat',
            'context_window' => 16000,
        ]);

        $module = CompositeModule::create([
            'name' => 'Fallback Flow',
            'slug' => 'fallback-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'node_1',
                        'type' => 'model',
                        'config' => [
                            'model_id' => $this->model->id,
                            'fallback_model_id' => $fallbackModel->id
                        ]
                    ]
                ],
                'edges' => [],
                'startNodeId' => 'node_1'
            ]
        ]);

        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->andThrow(new \Exception('Primary Failed'));
        $mockClient->shouldReceive('chat')
            ->once()
            ->andReturn(new AiResponse('Fallback Success', 1, 1, [], '0.000001'));

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [], $this->token);

        $this->assertEquals('Fallback Success', $result->output);
    }

    public function test_loop_node_executes_multiple_times()
    {
        $module = CompositeModule::create([
            'name' => 'Loop Flow',
            'slug' => 'loop-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'loop_1',
                        'type' => 'loop',
                        'config' => [
                            'max_iterations' => 3,
                            'loop_body_node_ids' => ['body_1'],
                            'break_condition' => 'variables.loop_iteration === 2'
                        ]
                    ],
                    [
                        'id' => 'body_1',
                        'type' => 'data_processor',
                        'config' => ['script' => 'variables.count = (variables.count || 0) + 1;']
                    ]
                ],
                'edges' => [],
                'startNodeId' => 'loop_1'
            ]
        ]);

        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [], $this->token);

        $this->assertEquals(3, $result->metadata['variables']['count']);
        $this->assertEquals(3, $result->metadata['iterations']);
    }
}
