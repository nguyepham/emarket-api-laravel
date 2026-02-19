<?php

namespace App\Http\Controllers;

use App\DTO\Auth\RegisterData;
use App\Exceptions\BadCredentialException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordForgotRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\AuthenticatedUser;
use App\Models\Auth\RefreshToken;
use App\Models\Auth\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController
{
    private function createAuthenticatedUser(User $user, ?string $existingToken = null): AuthenticatedUser
    {
        $accessToken = $existingToken ?? auth()->login($user);
        $refreshTokenText = Str::random(60);
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshTokenText
        ]);
        return new AuthenticatedUser(
            accessToken: $accessToken,
            refreshToken: $refreshTokenText,
            id: $user->id,
            email: $user->email,
            firstName: $user->first_name,
            lastName: $user->last_name
        );
    }

    public function register(RegisterRequest $request): AuthenticatedUser
    {
        return DB::transaction(function () use ($request) {
            $registerData = RegisterData::fromRequest($request);
            $user = User::create($registerData->toArray());
            $token = auth()->login($user);
            return $this->createAuthenticatedUser($user, $token);
        });
    }
    /**
     * @throws BadCredentialException
     */
    public function login(LoginRequest $request): AuthenticatedUser
    {
        $credential = $request->safe()->only('email', 'password');

        if (! $token = auth()->attempt($credential)) {
            throw new BadCredentialException();
        }
        return $this->createAuthenticatedUser(auth()->user(), $token);
    }

    /**
     * @throws AuthenticationException
     */
    public function refresh(RefreshTokenRequest $request): AuthenticatedUser
    {
        $refreshToken = $request->validated('refreshToken');
        $storedToken = RefreshToken::with('user')
            ->where('token', $refreshToken)
            ->first();

        if (! $storedToken || ! $storedToken->user) {
            throw new AuthenticationException();
        }

        $expirationMinutes = config('jwt.refresh_ttl', 20160);
        if ($storedToken->created_at->addMinutes($expirationMinutes)->isPast()) {
            $storedToken->delete();
            throw new AuthenticationException();
        }

        // Constantly rotate the refresh token.
        // This keeps active users constantly logged in and forces inactive users to re-log in.
        $user = $storedToken->user;
        $storedToken->delete();

        return $this->createAuthenticatedUser($user);
    }

    public function logout(RefreshTokenRequest $request)
    {
        Log::info('Logout request received', [
            'refresh_token' => $request->validated('refreshToken')
        ]);
        $userId = auth()->id();
        Log::info('Logout initiated', [
            'user_id' => $userId,
            'refresh_token' => $request->validated('refreshToken')
        ]);

        // Invalidate the JWT (Access Token) via Blacklist
        auth()->logout();

        // Revoke the specific Refresh Token.
        $refreshToken = $request->validated('refreshToken');
        $deleted = RefreshToken::where('token', $refreshToken)->delete();

        Log::info('Logout completed', [
            'user_id' => $userId,
            'refresh_token_revoked' => $deleted > 0
        ]);

        return response()->noContent();
    }

    public function forgotPassword(PasswordForgotRequest $request)
    {
        Log::info('Forgot password request initiated', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $status = Password::sendResetLink($request->safe()->only('email'));

        Log::info('Forgot password link sent attempt finished', [
            'email' => $request->email,
            'status' => $status
        ]);

        // Always return 204 to prevent email enumeration
        return response()->noContent();
    }

    public function resetPassword(PasswordResetRequest $request)
    {
        Log::info('Password reset request initiated', [
            'email' => $request->email,
            'token_preview' => substr($request->token, 0, 10) . '...'
        ]);

        $status = Password::reset(
            $request->safe()->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                Log::info('Password reset callback executed for user', ['user_id' => $user->id]);
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        Log::info('Password reset attempt finished', [
            'status' => $status,
            'success' => $status === Password::PASSWORD_RESET
        ]);

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mật khẩu đã được thay đổi thành công'], 200)
            : response()->json(['message' => __($status)], 422);
    }
}
