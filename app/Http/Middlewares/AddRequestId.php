<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Context;

class AddRequestId
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Get existing ID or generate a new one
        $requestId = $request->header('X-Request-Id') ?? (string) Str::uuid();

        // 2. "Hydrate" the request with this ID (so controllers can access it)
        $request->headers->set('X-Request-Id', $requestId);

        // 3. SaaS Magic: Add it to the Global Context
        // This ensures that ANY log written during this request will
        // automatically include specific metadata.
        Context::add('request_id', $requestId);

        // 4. Process the request
        $response = $next($request);

        // 5. Add the ID to the Response Header
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
