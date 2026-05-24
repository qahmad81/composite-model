<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\InternalToken;
use App\Models\InternalTransaction;
use App\Models\PendingReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class TokenService
{
    public function generateToken(int $clientId, string $name, array $settings = []): string
    {
        $rawToken = 'cm_' . Str::random(61);
        $hash = hash('sha256', $rawToken);

        InternalToken::create([
            'client_id' => $clientId,
            'token_hash' => $hash,
            'name' => $name,
            'settings' => $settings,
        ]);

        return $rawToken;
    }

    public function resolveToken(string $rawToken): ?InternalToken
    {
        if (!str_starts_with($rawToken, 'cm_')) {
            // UC integration will be added later
            return null;
        }

        $hash = hash('sha256', $rawToken);
        
        return InternalToken::where('token_hash', $hash)
            ->where('is_active', true)
            ->first();
    }

    public function reserve(InternalToken $token, int $amount, string $description = null): int
    {
        return DB::transaction(function () use ($token, $amount, $description) {
            $clientBalance = ClientBalance::where('client_id', $token->client_id)
                ->lockForUpdate()
                ->first();

            if (!$clientBalance) {
                throw new RuntimeException("Client balance not found.");
            }

            if ($clientBalance->pending_balance < $amount) {
                throw new RuntimeException("Insufficient balance.");
            }

            $lockedToken = InternalToken::where('id', $token->id)
                ->lockForUpdate()
                ->first();

            if ($lockedToken->limit_balance > 0 && ($lockedToken->pending_balance + $amount) > $lockedToken->limit_balance) {
                throw new RuntimeException("Token limit exceeded.");
            }

            // Deduct from client pending
            $clientBalance->decrement('pending_balance', $amount);

            // Add to token pending
            $lockedToken->increment('pending_balance', $amount);

            $reservation = PendingReservation::create([
                'internal_token_id' => $lockedToken->id,
                'amount' => $amount,
                'description' => $description,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10),
            ]);

            InternalTransaction::create([
                'client_id' => $token->client_id,
                'internal_token_id' => $token->id,
                'pending_reservation_id' => $reservation->id,
                'type' => 'reserve',
                'amount' => $amount,
                'balance_before' => $clientBalance->pending_balance + $amount,
                'balance_after' => $clientBalance->pending_balance,
                'description' => $description,
            ]);

            return $reservation->id;
        });
    }

    public function confirm(int $reservationId, int $actualAmount): void
    {
        DB::transaction(function () use ($reservationId, $actualAmount) {
            $reservation = PendingReservation::where('id', $reservationId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                throw new RuntimeException("Reservation not found or not pending.");
            }

            $token = InternalToken::where('id', $reservation->internal_token_id)
                ->lockForUpdate()
                ->first();

            $clientBalance = ClientBalance::where('client_id', $token->client_id)
                ->lockForUpdate()
                ->first();

            $reservedAmount = $reservation->amount;
            $finalAmount = min($actualAmount, $reservedAmount);
            $refundAmount = $reservedAmount - $finalAmount;

            // Adjust balances
            // Client: final balance decreases by finalAmount. Pending balance already decreased by reservedAmount.
            // Refund the difference back to pending balance.
            $clientBalance->decrement('final_balance', $finalAmount);
            if ($refundAmount > 0) {
                $clientBalance->increment('pending_balance', $refundAmount);
            }

            // Token: final balance increases by finalAmount. Pending balance decreases by reservedAmount.
            $token->increment('final_balance', $finalAmount);
            $token->decrement('pending_balance', $reservedAmount);

            $reservation->update(['status' => 'confirmed']);

            InternalTransaction::create([
                'client_id' => $token->client_id,
                'internal_token_id' => $token->id,
                'pending_reservation_id' => $reservation->id,
                'type' => 'confirm',
                'amount' => $finalAmount,
                'balance_before' => $clientBalance->final_balance + $finalAmount,
                'balance_after' => $clientBalance->final_balance,
                'description' => $actualAmount > $reservedAmount ? "Confirmed (capped at $reservedAmount)" : null,
            ]);

            if ($refundAmount > 0) {
                InternalTransaction::create([
                    'client_id' => $token->client_id,
                    'internal_token_id' => $token->id,
                    'pending_reservation_id' => $reservation->id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'balance_before' => $clientBalance->pending_balance - $refundAmount,
                    'balance_after' => $clientBalance->pending_balance,
                    'description' => "Refund from reservation $reservationId",
                ]);
            }
        });
    }

    public function cancel(int $reservationId): void
    {
        DB::transaction(function () use ($reservationId) {
            $reservation = PendingReservation::where('id', $reservationId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                throw new RuntimeException("Reservation not found or not pending.");
            }

            $token = InternalToken::where('id', $reservation->internal_token_id)
                ->lockForUpdate()
                ->first();

            $clientBalance = ClientBalance::where('client_id', $token->client_id)
                ->lockForUpdate()
                ->first();

            $amount = $reservation->amount;

            // Refund full amount to client pending balance
            $clientBalance->increment('pending_balance', $amount);

            // Deduct from token pending balance
            $token->decrement('pending_balance', $amount);

            $reservation->update(['status' => 'cancelled']);

            InternalTransaction::create([
                'client_id' => $token->client_id,
                'internal_token_id' => $token->id,
                'pending_reservation_id' => $reservation->id,
                'type' => 'cancel',
                'amount' => $amount,
                'balance_before' => $clientBalance->pending_balance - $amount,
                'balance_after' => $clientBalance->pending_balance,
                'description' => "Cancelled reservation $reservationId",
            ]);
        });
    }

    public function modifyReservation(int $reservationId, int $newAmount): int
    {
        return DB::transaction(function () use ($reservationId, $newAmount) {
            $reservation = PendingReservation::where('id', $reservationId)
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                throw new RuntimeException("Reservation not found.");
            }

            $token = InternalToken::where('id', $reservation->internal_token_id)
                ->lockForUpdate()
                ->first();

            $clientBalance = ClientBalance::where('client_id', $token->client_id)
                ->lockForUpdate()
                ->first();

            $diff = $newAmount - $reservation->amount;

            if ($diff > 0) {
                if ($clientBalance->pending_balance < $diff) {
                    throw new RuntimeException("Insufficient balance to increase reservation.");
                }
                
                if ($token->limit_balance > 0 && ($token->pending_balance + $diff) > $token->limit_balance) {
                    throw new RuntimeException("Token limit exceeded.");
                }

                $clientBalance->decrement('pending_balance', $diff);
                $token->increment('pending_balance', $diff);
            } elseif ($diff < 0) {
                $absDiff = abs($diff);
                $clientBalance->increment('pending_balance', $absDiff);
                $token->decrement('pending_balance', $absDiff);
            }

            $reservation->update(['amount' => $newAmount]);

            InternalTransaction::create([
                'client_id' => $token->client_id,
                'internal_token_id' => $token->id,
                'pending_reservation_id' => $reservation->id,
                'type' => 'reserve_adjust',
                'amount' => $diff,
                'balance_before' => $clientBalance->pending_balance + ($diff > 0 ? $diff : 0),
                'balance_after' => $clientBalance->pending_balance,
                'description' => "Adjusted reservation $reservationId to $newAmount",
            ]);

            return $reservation->id;
        });
    }

    public function topup(int $clientId, int $amount): void
    {
        DB::transaction(function () use ($clientId, $amount) {
            $clientBalance = ClientBalance::firstOrCreate(
                ['client_id' => $clientId],
                ['final_balance' => 0, 'pending_balance' => 0]
            );

            $clientBalance->increment('final_balance', $amount);
            $clientBalance->increment('pending_balance', $amount);

            InternalTransaction::create([
                'client_id' => $clientId,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $clientBalance->final_balance - $amount,
                'balance_after' => $clientBalance->final_balance,
                'description' => 'Topup',
            ]);
        });
    }
}