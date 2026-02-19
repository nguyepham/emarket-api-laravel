<?php

namespace App\Http\Controllers;

use App\Exceptions\BadCredentialException;
use App\Http\Requests\Auth\PasswordChangeRequest;
use App\Models\Auth\RefreshToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MeController
{
    /**
     * @throws BadCredentialException
     */
    public function changePassword(PasswordChangeRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = auth()->user();
            // Validate the current password
            if (! Hash::check($request->get('currentPassword'), $user->password)) {
                throw new BadCredentialException();
            }
            // Save the new password to the database
            $user->forceFill([
                'password' => Hash::make($request->get('newPassword'))
            ])->save();
            // Revoke every refresh token of this user, effectively log them out of all devices.
            auth()->logout();
            RefreshToken::where('user_id', $user->id)->delete();

            return response()->noContent();
        });
    }
}
