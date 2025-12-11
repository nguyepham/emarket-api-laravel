<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Assuming that the route is already protected by middleware (e.g., 'auth:api').
        return true;
    }

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string'],

            'newPassword' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * Useful for mapping camelCase inputs to readable error messages.
     */
    public function attributes(): array
    {
        return [
            'currentPassword' => 'current password',
            'newPassword' => 'new password',
        ];
    }
}
