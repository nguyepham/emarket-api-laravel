<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\SecureHealthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

Route::prefix('v1')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware('auth:api')->group(function () {
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });
    Route::middleware('auth:api')->group(function () {
        Route::get('/secure-health', [SecureHealthController::class, 'liveness']);
        // Routes for the current authenticated user
        Route::prefix('/me')->group(function () {
            Route::put('/password', [MeController::class, 'changePassword']);
        });
    });
});

Route::get('/debug-jwt', function () {
    // 1. Check if the header exists (Server Config Check)
    $token = request()->bearerToken();
    if (! $token) {
        return response()->json(['error' => 'Authorization header is missing or stripped by server.'], 400);
    }

    try {
        // 2. Attempt to parse manually
        $payload = JWTAuth::setToken($token)->getPayload();

        // 3. Check if User exists (Database Integrity Check)
        if (! auth('api')->user()) {
            return response()->json([
                'error' => 'Token is valid, but User ID not found in DB.',
                'sub_claim' => $payload->get('sub')
            ], 404);
        }

        return response()->json([
            'status' => 'Token is VALID',
            'payload' => $payload->toArray()
        ]);

    } catch (TokenExpiredException $e) {
        return response()->json(['error' => 'Token Expired'], 401);
    } catch (TokenInvalidException $e) {
        return response()->json(['error' => 'Token Signature Invalid (Secret Mismatch)'], 401);
    } catch (\Exception $e) {
        return response()->json(['error' => 'General Error: ' . $e->getMessage()], 500);
    }
});
