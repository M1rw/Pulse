<?php
/**
 * CSRF Middleware.
 * 
 * POST/PUT/DELETE/PATCH must have a valid token.
 * GET/HEAD/OPTIONS are exempt (they shouldn't mutate state).
 * AJAX requests can pass the token in X-CSRF-Token header.
 */
namespace App\Http\Middleware;

use App\Http\Request;
use App\Core\Exceptions\CsrfException;

class CsrfMiddleware implements MiddlewareInterface
{
    // methods that need CSRF protection
    private const PROTECTED_METHODS = ['POST', 'PUT', 'DELETE', 'PATCH'];

    public function handle(Request $request, \Closure $next): mixed
    {
        // safe methods - skip check
        if (!in_array($request->method(), self::PROTECTED_METHODS)) {
            return $next($request);
        }

        $token = $request->input('_token') 
            ?? $request->header('X-CSRF-Token') 
            ?? '';

        $sessionToken = $_SESSION['_csrf_token'] ?? '';

        // timing-safe comparison
        if (!hash_equals($sessionToken, $token)) {
            throw new CsrfException('CSRF token mismatch. Either your session expired or something sketchy is going on.');
        }

        return $next($request);
    }
}