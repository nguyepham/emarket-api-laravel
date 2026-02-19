<?php

use App\Enums\ExceptionName;
use App\Http\Responses\ErrorResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Responses\ErrorItem;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS must be handled globally to catch preflight OPTIONS requests
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->append(\App\Http\Middlewares\AddRequestId::class);

        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            // Use the static factory method created earlier
            // It automatically converts the complex Laravel error bag
            // into the "ErrorResponse" structure.
            return ErrorResponse::fromValidator($e->validator);
        });
        $exceptions->render(function (\App\Exceptions\BadCredentialException $e) {
            return new ErrorResponse(401, [new ErrorItem(
                message: $e->getMessage(),
                exception: ExceptionName::BadCredential->value)]
            );
        });
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e) {
            return new ErrorResponse(401, [new ErrorItem(
                message: 'API Token is missing or invalid.',
                exception: ExceptionName::Unauthenticated->value)]
            );
        });
        $exceptions->render(function (ModelNotFoundException $e) {
            return new ErrorResponse(404, [new ErrorItem(
                message: 'Resource does not exist: ' . $e->getModel() . ': ' . $e->getIds()[0],
                exception: ExceptionName::ModelNotFound->value)]
            );
        });
        $exceptions->render(function (Throwable $e) {
            $message = app()->environment(['local', 'testing']) ? $e->getMessage() : 'Server Error. Please try again later.';

            return new ErrorResponse(500, [new ErrorItem(
                message: $message,
                exception: get_class($e),
                file: $e->getFile(),
                line: $e->getLine(),
                trace: $e->getTrace()
            )]);
        });
    })->create();
