<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class SecureHealthController
{
    /**
     * Authenticated Liveness probe.
     * * @return JsonResponse
     */
    public function liveness(): JsonResponse
    {
        // auth()->user() will return a User object here because the 'auth:api'
        // middleware successfully validated the token and retrieved the user.
        // If the token was expired, the request would have been rejected
        // before reaching this method.

        return response()->json([
            'status' => 'ok',
            // Use Carbon to provide a clean, standardized timestamp
            'time' => Carbon::now()->toIso8601String(),
        ]);
    }
}
