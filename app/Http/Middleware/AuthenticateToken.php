<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawToken = $request->bearerToken();

        if (!$rawToken) {
            return response()->json(['message' => 'Unauthorized: Token missing'], 401);
        }

        $token = $this->tokenService->resolveToken($rawToken);

        if (!$token || !$token->is_active) {
            return response()->json(['message' => 'Unauthorized: Invalid or inactive token'], 401);
        }

        // Attach token and client to request
        $request->attributes->add([
            'internal_token' => $token,
            'client' => $token->client,
        ]);

        return $next($request);
    }
}