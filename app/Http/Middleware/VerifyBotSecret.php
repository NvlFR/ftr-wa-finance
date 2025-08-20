<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBotSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $receivedSecret = $request->input('secret');
        $expectedSecret = env('BOT_API_SECRET');

        if ($receivedSecret !== $expectedSecret) {
            return response()->json([
                'error' => 'Unauthorized',
                'debug_received' => $receivedSecret,
                'debug_expected_is_null' => is_null($expectedSecret),
                'debug_expected_is_false' => $expectedSecret === false,
            ], 401);
        }

        // Jika cocok, lanjutkan permintaan ke controller
        return $next($request);
    }
}
