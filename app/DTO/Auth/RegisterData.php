<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;

readonly class RegisterData
{
    public function __construct(
        public string  $firstName,
        public string  $lastName,
        public string  $email,
        public string  $password,
        public ?string $phone = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            firstName: $request->input('firstName'),
            lastName:  $request->input('lastName'),
            email:      $request->input('email'),
            password:   $request->input('password'),
            phone:      $request->input('phone'),
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'email'      => $this->email,
            'password'   => $this->password,
            'phone'      => $this->phone,
        ];
    }
}
