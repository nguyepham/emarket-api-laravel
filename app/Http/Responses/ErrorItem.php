<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\App;
use JsonSerializable;

readonly class ErrorItem implements JsonSerializable
{
    public function __construct(
        public string $message,
        public ?string $field = null,
        public ?string $exception = null,
        public ?string $file = null,
        public ?int $line = null,
        public ?array $trace = null
    ) {}

    public function toArray(): array
    {
        // Determine if we should show debug info
        $showDebug = ! App::environment('production');

        $data = [
            'message' => $this->message,
            'field' => $this->field,
            // Conditionally include debug info
            'exception' => $showDebug ? $this->exception : null,
            'file'      => $showDebug ? $this->file : null,
            'line'      => $showDebug ? $this->line : null,
            'trace'     => $showDebug ? $this->trace : null,
        ];

        // Filter out null values
        // This ensures that in production, the keys 'exception', 'file', etc.
        // simply do not exist in the JSON output.
        return array_filter($data, fn ($value) => !is_null($value));
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
