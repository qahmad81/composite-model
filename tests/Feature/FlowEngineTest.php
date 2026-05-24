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

class FlowEngineTest extends TestCase
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

    public function test_single_model_node_execution()
    {
        $module = CompositeModule::create([
            'name' => 'Single Model Flow',
            'slug' => 'single-model',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'node_1',
                        'type' => 'model',
                        'config' => ['model_id' => $this->model->id]
                    ]
                ],
                'edges' => [],
                'startNodeId' => 'node_1'
            ]
        ]);

        $mockResponse = new AiResponse('Hello from AI', 10, 20, [], '0.000030');
        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')->andReturn($mockResponse);

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $reservationId = app(TokenService::class)->reserve($this->token, 1000);
        
        /** @var FlowEngine $engine */
        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [['role' => 'user', 'content' => 'Hi']], $this->token, $reservationId);

        $this->assertEquals('Hello from AI', $result->output);
        $this->assertEquals(30, $result->cost);
        $this->assertDatabaseHas('execution_logs', [
            'node_id' => 'node_1',
            'status' => 'success'
        ]);
    }

    public function test_two_serial_nodes()
    {
        $module = CompositeModule::create([
            'name' => 'Serial Flow',
            'slug' => 'serial-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'node_1',
                        'type' => 'model',
                        'config' => ['model_id' => $this->model->id]
                    ],
                    [
                        'id' => 'node_2',
                        'type' => 'model',
                        'config' => ['model_id' => $this->model->id]
                    ]
                ],
                'edges' => [
                    ['source' => 'node_1', 'target' => 'node_2']
                ],
                'startNodeId' => 'node_1'
            ]
        ]);

        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')
            ->twice()
            ->andReturn(
                new AiResponse('First Response', 10, 10, [], '0.000020'),
                new AiResponse('Second Response', 20, 20, [], '0.000040')
            );

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $reservationId = app(TokenService::class)->reserve($this->token, 1000);
        
        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [['role' => 'user', 'content' => 'Hi']], $this->token, $reservationId);

        $this->assertEquals('Second Response', $result->output);
        $this->assertEquals(60, $result->cost);
        $this->assertDatabaseCount('execution_logs', 2);
    }

    public function test_condition_node_routing()
    {
        $module = CompositeModule::create([
            'name' => 'Conditional Flow',
            'slug' => 'cond-flow',
            'flow_json' => [
                'nodes' => [
                    [
                        'id' => 'node_start',
                        'type' => 'data_processor',
                        'config' => ['script' => 'variables.score = 85;']
                    ],
                    [
                        'id' => 'node_cond',
                        'type' => 'condition',
                        'config' => [
                            'expression' => 'variables.score > 80',
                            'true_branch' => 'node_true',
                            'false_branch' => 'node_false'
                        ]
                    ],
                    [
                        'id' => 'node_true',
                        'type' => 'model',
                        'config' => ['model_id' => $this->model->id]
                    ],
                    [
                        'id' => 'node_false',
                        'type' => 'model',
                        'config' => ['model_id' => $this->model->id]
                    ]
                ],
                'edges' => [
                    ['source' => 'node_start', 'target' => 'node_cond']
                ],
                'startNodeId' => 'node_start'
            ]
        ]);

        $mockClient = Mockery::mock(ProviderClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->andReturn(new AiResponse('High Score Response', 10, 10, [], '0.000020'));

        $this->mock(ProviderClientManager::class, function ($mock) use ($mockClient) {
            $mock->shouldReceive('resolve')->andReturn($mockClient);
        });

        $reservationId = app(TokenService::class)->reserve($this->token, 1000);
        
        $engine = app(FlowEngine::class);
        $result = $engine->execute($module, [['role' => 'user', 'content' => 'Hi']], $this->token, $reservationId);

        $this->assertEquals('High Score Response', $result->output);
        $this->assertDatabaseHas('execution_logs', ['node_id' => 'node_true']);
        $this->assertDatabaseMissing('execution_logs', ['node_id' => 'node_false']);
    }
}
