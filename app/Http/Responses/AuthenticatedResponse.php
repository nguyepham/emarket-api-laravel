<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

readonly class AuthenticatedResponse implements JsonSerializable, Arrayable
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken
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
