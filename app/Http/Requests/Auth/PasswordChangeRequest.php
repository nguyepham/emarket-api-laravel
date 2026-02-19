<?php

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

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

            // At least 4 of 5 conditions must be met (mirrors frontend logic).
            'newPassword' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    $conditions = [
                        'length'    => mb_strlen($value) >= 8,
                        'lowercase' => (bool) preg_match('/[a-z]/', $value),
                        'uppercase' => (bool) preg_match('/[A-Z]/', $value),
                        'number'    => (bool) preg_match('/\d/', $value),
                        'special'   => (bool) preg_match('/[\W_]/', $value),
                    ];

                    $metCount = count(array_filter($conditions));

                    if ($metCount < 4) {
                        $fail('The password must meet at least 4 of the following: 8+ characters, lowercase letter, uppercase letter, number, special character.');
                    }
                },
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
