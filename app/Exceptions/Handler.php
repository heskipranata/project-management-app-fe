<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Robust API detection: accept header, ajax, wantsJson, Authorization header,
        // or request URI starting with /api to force JSON responses for API routes.
        $uri = $request->getRequestUri();
        $accept = $request->header('Accept', '');
        $hasAuthHeader = $request->header('Authorization') || $request->bearerToken();

        $isApiRequest = $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || $request->is('api/*')
            || str_starts_with($uri, '/api')
            || str_contains($uri, '/api/')
            || str_contains(strtolower($accept), 'application/json')
            || $hasAuthHeader;

        if ($isApiRequest) {
            if ($exception instanceof AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($exception instanceof AuthorizationException) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            if ($exception instanceof TokenExpiredException) {
                return response()->json(['message' => 'Token expired'], 401);
            }

            if ($exception instanceof TokenInvalidException || $exception instanceof TokenBlacklistedException) {
                return response()->json(['message' => 'Token invalid'], 401);
            }

            if ($exception instanceof JWTException) {
                return response()->json(['message' => $exception->getMessage() ?: 'Token error'], 401);
            }

            if ($exception instanceof HttpException) {
                return response()->json(['message' => $exception->getMessage() ?: 'HTTP Error'], $exception->getStatusCode());
            }

            return response()->json(['message' => 'Server error'], 500);
        }

        return parent::render($request, $exception);
    }
}
