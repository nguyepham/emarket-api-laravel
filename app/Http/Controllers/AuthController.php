<?php

namespace App\Http\Controllers;

use App\DTO\Auth\RegisterData;
use App\Exceptions\BadCredentialException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\AuthenticatedResponse;
use App\Models\Auth\RefreshToken;
use App\Models\Auth\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController
{
    private function issueTokens(User $user, ?string $existingToken = null): AuthenticatedResponse
    {
        $accessToken = $existingToken ?? auth()->login($user);
        $refreshTokenText = Str::random(60);
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshTokenText
        ]);
        return new AuthenticatedResponse(
            accessToken: $accessToken,
            refreshToken: $refreshTokenText
        );
    }

    public function register(RegisterRequest $request): AuthenticatedResponse
    {
        return DB::transaction(function () use ($request) {
            $registerData = RegisterData::fromRequest($request);
            $user = User::create($registerData->toArray());
            $token = auth()->login($user);
            return $this->issueTokens($user, $token);
        });
    }
    /**
     * @throws BadCredentialException
     */
    public function login(LoginRequest $request): AuthenticatedResponse
    {
        $credential = $request->only('email', 'password');

        if (! $token = auth()->attempt($credential)) {
            throw new BadCredentialException();
        }
        return $this->issueTokens(auth()->user(), $token);
    }

    /**
     * @throws AuthenticationException
     */
    public function refresh(RefreshTokenRequest $request): AuthenticatedResponse
    {
        $storedToken = RefreshToken::where('token', $request->only('refreshToken'))->first();
        if (! $storedToken) {
            throw new AuthenticationException();
        }
        $expirationMinutes = config('jwt.refresh_ttl', 20160);
        if ($storedToken->created_at->addMinutes($expirationMinutes)->isPast()) {
            $storedToken->delete(); //
            throw new AuthenticationException();
        }
        // Constantly rotate the refresh token.
        // This keeps active users constantly logged in and forces inactive users to re-log in.
        $storedToken->delete();
        return $this->issueTokens(auth()->user());
    }

    public function logout(RefreshTokenRequest $request)
    {
        // Invalidate the JWT (Access Token) via Blacklist
        auth()->logout();
        // Revoke the specific Refresh Token.
        RefreshToken::where('token', $request->only('refreshToken'))->delete();
        return response()->noContent();
    }
}
