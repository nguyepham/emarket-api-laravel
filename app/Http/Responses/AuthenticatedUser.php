<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

readonly class AuthenticatedUser implements JsonSerializable, Arrayable
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $id,
        public string $email,
        public string $firstName,
        public string $lastName
    ) {}

    /**
     * Convert the object to an array.
     * This maps the PHP properties to the OAS keys.
     */
    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'refreshToken' => $this->refreshToken,
            'id' => $this->id,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }

    /**
     * Serialize to JSON.
     * Allows for returning this object directly from a controller.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
