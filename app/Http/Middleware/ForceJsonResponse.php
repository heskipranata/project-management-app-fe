<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Force requests to accept JSON so exception handler returns JSON responses.
     */
    public function handle(Request $request, Closure $next)
    {
        // Ensure Accept header contains application/json
        $accept = $request->header('Accept');
        if (! $accept || stripos($accept, 'application/json') === false) {
            $request->headers->set('Accept', 'application/json');
            $request->server->set('HTTP_ACCEPT', 'application/json');
        }

        if (! $request->header('X-Requested-With')) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            $request->server->set('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
        }

        return $next($request);
    }
}
