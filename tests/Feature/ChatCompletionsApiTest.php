<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\CompositeModule;
use App\Models\InternalToken;
use App\Models\SiteSetting;
use App\Services\Flow\FlowEngine;
use App\Services\Flow\NodeResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatCompletionsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        SiteSetting::create(['key' => 'internal_multiplier', 'value' => '1.5']);
        SiteSetting::create(['key' => 'uc_multiplier', 'value' => '1.2']);
    }

    public function test_non_streaming_returns_openai_format(): void
    {
        $client = Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
        ClientBalance::create(['client_id' => $client->id, 'final_balance' => 10000, 'pending_balance' => 10000]);
        
        $tokenRaw = 'cm_' . str_repeat('a', 61);
        $hash = hash('sha256', $tokenRaw);
        $token = InternalToken::create([
            'client_id' => $client->id,
            'token_hash' => $hash,
            'name' => 'Default',
            'is_active' => true
        ]);

        $module = CompositeModule::create([
            'name' => 'Test Module',
            'slug' => 'test-model',
            'flow_json' => ['nodes' => [['id' => '1', 'type' => 'model']]],
            'top_credit_limit' => 1000,
            'is_enabled' => true
        ]);

        $mockResult = new NodeResult(
            output: 'Hello world',
            tokens_input: 10,
            tokens_output: 20,
            cost: 100
        );

        $this->mock(FlowEngine::class, function ($mock) use ($mockResult) {
            $mock->shouldReceive('execute')->once()->andReturn($mockResult);
        });

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenRaw)
            ->postJson('/api/v1/chat/completions', [
                'model' => 'test-model',
                'messages' => [['role' => 'user', 'content' => 'hi']],
                'stream' => false
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'object', 'created', 'model',
                'choices' => [['index', 'message' => ['role', 'content'], 'finish_reason']],
                'usage' => ['prompt_tokens', 'completion_tokens', 'total_tokens']
            ])
            ->assertJsonPath('choices.0.message.content', 'Hello world');
    }

    public function test_streaming_returns_sse_chunks(): void
    {
        $client = Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
        ClientBalance::create(['client_id' => $client->id, 'final_balance' => 10000, 'pending_balance' => 10000]);
        
        $tokenRaw = 'cm_' . str_repeat('a', 61);
        $hash = hash('sha256', $tokenRaw);
        $token = InternalToken::create([
            'client_id' => $client->id,
            'token_hash' => $hash,
            'name' => 'Default',
            'is_active' => true
        ]);

        CompositeModule::create([
            'name' => 'Test Module',
            'slug' => 'test-model',
            'flow_json' => ['nodes' => [['id' => '1', 'type' => 'model']]],
            'top_credit_limit' => 1000,
            'is_enabled' => true
        ]);

        $mockResult = new NodeResult(
            output: 'Stream me',
            tokens_input: 10,
            tokens_output: 20,
            cost: 100
        );

        $this->mock(FlowEngine::class, function ($mock) use ($mockResult) {
            $mock->shouldReceive('execute')->once()->andReturn($mockResult);
        });

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenRaw)
            ->postJson('/api/v1/chat/completions', [
                'model' => 'test-model',
                'messages' => [['role' => 'user', 'content' => 'hi']],
                'stream' => true
            ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('data: {"id":', $content);
        $this->assertStringContainsString('"delta":{"content":"Stream"}', $content);
        $this->assertStringContainsString('data: [DONE]', $content);
    }

    public function test_invalid_model_returns_404(): void
    {
        $client = Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
        $tokenRaw = 'cm_' . str_repeat('a', 61);
        $hash = hash('sha256', $tokenRaw);
        InternalToken::create([
            'client_id' => $client->id,
            'token_hash' => $hash,
            'name' => 'Default',
            'is_active' => true
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenRaw)
            ->postJson('/api/v1/chat/completions', [
                'model' => 'non-existent',
                'messages' => [['role' => 'user', 'content' => 'hi']]
            ]);

        $response->assertStatus(404)
            ->assertJsonPath('error.code', 'model_not_found');
    }

    public function test_insufficient_balance_returns_402(): void
    {
        $client = Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
        ClientBalance::create(['client_id' => $client->id, 'final_balance' => 100, 'pending_balance' => 100]);
        
        $tokenRaw = 'cm_' . str_repeat('a', 61);
        $hash = hash('sha256', $tokenRaw);
        InternalToken::create([
            'client_id' => $client->id,
            'token_hash' => $hash,
            'name' => 'Default',
            'is_active' => true
        ]);

        CompositeModule::create([
            'name' => 'Test Module',
            'slug' => 'test-model',
            'flow_json' => ['nodes' => [['id' => '1', 'type' => 'model']]],
            'top_credit_limit' => 1000,
            'is_enabled' => true
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenRaw)
            ->postJson('/api/v1/chat/completions', [
                'model' => 'test-model',
                'messages' => [['role' => 'user', 'content' => 'hi']]
            ]);

        $response->assertStatus(402)
            ->assertJsonPath('error.code', 'insufficient_balance');
    }

    public function test_models_endpoint_lists_modules(): void
    {
        $client = Client::create(['name' => 'Test Client', 'email' => 'test@example.com']);
        $tokenRaw = 'cm_' . str_repeat('a', 61);
        $hash = hash('sha256', $tokenRaw);
        InternalToken::create([
            'client_id' => $client->id,
            'token_hash' => $hash,
            'name' => 'Default',
            'is_active' => true
        ]);

        CompositeModule::create([
            'name' => 'Module 1',
            'slug' => 'model-1',
            'flow_json' => [],
            'top_credit_limit' => 1000,
            'is_enabled' => true
        ]);

        CompositeModule::create([
            'name' => 'Module 2',
            'slug' => 'model-2',
            'flow_json' => [],
            'top_credit_limit' => 1000,
            'is_enabled' => false
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenRaw)
            ->getJson('/api/v1/models');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 'model-1');
    }
}
