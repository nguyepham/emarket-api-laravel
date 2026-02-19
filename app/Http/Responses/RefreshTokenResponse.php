<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

readonly class RefreshTokenResponse implements JsonSerializable, Arrayable
{
    public function __construct(
        public string $accessToken
    ) {}

    /**
     * Convert the object to an array.
     * This maps the PHP properties to the OAS keys.
     */
    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken
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
