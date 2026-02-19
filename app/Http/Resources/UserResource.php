<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method maps the internal database columns to the
     * external OAS schema keys.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Direct pass-through
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,

            // Snake_case -> camelCase transformation
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,

            // Logic transformation (Timestamp -> Boolean)
            'emailVerified' => ! is_null($this->email_verified_at),

            // Type safety (Ensure roles is always an array)
            'roles' => $this->roles ?? [],

            // Format enforcement (OAS 'date-time' usually requires ISO 8601)
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
