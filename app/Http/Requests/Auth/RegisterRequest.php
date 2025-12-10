<?php

namespace App\Http\Requests\Auth;

use App\Models\Auth\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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

            // 2. Enforce Complexity
            // Use the Password rule object instead of a raw Regex.
            // It maps perfectly to the OAS requirements and is more readable.
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase() // Requires at least 1 uppercase and 1 lowercase
                    ->numbers()   // Requires at least 1 number
                    ->symbols()   // Requires at least 1 special character
            ],

            'phone' => ['string'],
            'firstName' => ['required', 'string', 'min:1'],
            'lastName' => ['required', 'string', 'min:1'],
        ];
    }
}
