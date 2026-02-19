<?php

namespace App\Http\Requests\Auth;

use App\Models\Auth\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 1. Enforce Uniqueness
            // 'unique:users,email' checks the 'users' table, 'email' column.
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique(User::class, 'email')
            ],

            // 2. Enforce Complexity — at least 4 of 5 conditions must be met.
            // This mirrors the frontend PasswordStrengthIndicator logic exactly.
            'password' => [
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
                        $fail('Vui lòng chọn mật khẩu mạnh hơn');
                    }
                },
            ],

            'phone' => ['string'],
            'firstName' => ['required', 'string', 'min:1'],
            'lastName' => ['required', 'string', 'min:1'],
        ];
    }
}

