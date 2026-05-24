<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\InternalToken;
use App\Models\PendingReservation;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TokenService $tokenService;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = new TokenService();
        $this->client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@example.com',
        ]);
        ClientBalance::create([
            'client_id' => $this->client->id,
            'final_balance' => 0,
            'pending_balance' => 0,
        ]);
    }

    public function test_topup_increases_balance(): void
    {
        $this->tokenService->topup($this->client->id, 1000);

        $balance = ClientBalance::where('client_id', $this->client->id)->first();
        $this->assertEquals(1000, $balance->final_balance);
        $this->assertEquals(1000, $balance->pending_balance);
    }

    public function test_reserve_success(): void
    {
        $this->tokenService->topup($this->client->id, 1000);
        $rawToken = $this->tokenService->generateToken($this->client->id, 'Test Token');
        $token = $this->tokenService->resolveToken($rawToken);

        $reservationId = $this->tokenService->reserve($token, 500, 'Test Reservation');

        $this->assertDatabaseHas('pending_reservations', [
            'id' => $reservationId,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $balance = ClientBalance::where('client_id', $this->client->id)->first();
        $this->assertEquals(500, $balance->pending_balance);
        $this->assertEquals(1000, $balance->final_balance);

        $tokenBalance = InternalToken::find($token->id);
        $this->assertEquals(500, $tokenBalance->pending_balance);
    }

    public function test_reserve_insufficient_balance(): void
    {
        $this->tokenService->topup($this->client->id, 400);
        $rawToken = $this->tokenService->generateToken($this->client->id, 'Test Token');
        $token = $this->tokenService->resolveToken($rawToken);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient balance.');

        $this->tokenService->reserve($token, 500);
    }

    public function test_reserve_exceeds_token_limit(): void
    {
        $this->tokenService->topup($this->client->id, 1000);
        $rawToken = $this->tokenService->generateToken($this->client->id, 'Test Token');
        $token = $this->tokenService->resolveToken($rawToken);
        $token->update(['limit_balance' => 300]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token limit exceeded.');

        $this->tokenService->reserve($token, 500);
    }

    public function test_confirm_success(): void
    {
        $this->tokenService->topup($this->client->id, 1000);
        $rawToken = $this->tokenService->generateToken($this->client->id, 'Test Token');
        $token = $this->tokenService->resolveToken($rawToken);

        $reservationId = $this->tokenService->reserve($token, 500);
        $this->tokenService->confirm($reservationId, 450);

        $balance = ClientBalance::where('client_id', $this->client->id)->first();
        $this->assertEquals(550, $balance->pending_balance); // 1000 - 500 + (500 - 450)
        $this->assertEquals(550, $balance->final_balance);   // 1000 - 450

        $tokenBalance = InternalToken::find($token->id);
        $this->assertEquals(0, $tokenBalance->pending_balance);
        $this->assertEquals(450, $tokenBalance->final_balance);

        $this->assertDatabaseHas('pending_reservations', [
            'id' => $reservationId,
            'status' => 'confirmed',
        ]);
    }

    public function test_confirm_caps_at_reserved(): void
    {
        $this->tokenService->topup($this->client->id, 1000);
        $rawToken = $this->tokenService->generateToken($this->client->id, 'Test Token');
        $token = $this->tokenService->resolveToken($rawToken);

        $reservationId = $this->tokenService->reserve($token, 500);
        $this->tokenService->confirm($reservationId, 600);

        $balance = ClientBalance::where('client_id', $this->client->id)->first();
        $this->assertEquals(500, $balance->pending_balance); // 1000 - 500
        $this->assertEquals(500, $balance->final_balance);   // 1000 - 500

        $tokenBalance = InternalToken::find($token->id);
        $this->assertEquals(0, $tokenBalance->pending_balance);
        $this->assertEquals(500, $tokenBalance->final_balance);
    }

    public function test_cancel_refunds_full_amount(): void
    {
        $this->tokenService->topup($this->client->id, 1000);
        $rawToken = $this->tokenService->generateToken($this->client->id, 'Test Token');
        $token = $this->tokenService->resolveToken($rawToken);

        $reservationId = $this->tokenService->reserve($token, 500);
        $this->tokenService->cancel($reservationId);

        $balance = ClientBalance::where('client_id', $this->client->id)->first();
        $this->assertEquals(1000, $balance->pending_balance);
        $this->assertEquals(1000, $balance->final_balance);

        $tokenBalance = InternalToken::find($token->id);
        $this->assertEquals(0, $tokenBalance->pending_balance);
        $this->assertEquals(0, $tokenBalance->final_balance);

        $this->assertDatabaseHas('pending_reservations', [
            'id' => $reservationId,
            'status' => 'cancelled',
        ]);
    }
}