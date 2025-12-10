<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use JsonSerializable;

readonly class ErrorResponse implements JsonSerializable, Responsable
{
    /**
     * @param int $httpStatus
     * @param ErrorItem[] $errors An array of ErrorItem objects
     */
    public function __construct(
        public int $httpStatus,
        public array $errors
    ) {
        if ($this->httpStatus < 100 || $this->httpStatus > 600) {
            throw new InvalidArgumentException("HTTP Status must be between 100 and 600.");
        }
    }

    /**
     * Create a response from a Laravel Validator instance.
     *
     * @param Validator $validator
     * @return ErrorResponse
     */
    public static function fromValidator(\Illuminate\Contracts\Validation\Validator $validator): self
    {
        $errorItems = [];

        // Laravel returns errors grouped by field: ['email' => ['Invalid format']]
        foreach ($validator->errors()->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $errorItems[] = new ErrorItem(message: $message, field: $field);
            }
        }

        return new self(httpStatus: 422, errors: $errorItems);
    }

    public function toArray(): array
    {
        return [
            'httpStatus' => $this->httpStatus,
            'errors' => array_map(
                fn (ErrorItem $item) => $item->toArray(),
                $this->errors
            ),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: $this->toArray(),
            status: $this->httpStatus
        );
    }
}
