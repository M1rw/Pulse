<?php
/**
 * Session Middleware.
 * 
 * Starts PHP sessions with a hardening config I researched.
 * Not just session_start() and done.
 */
namespace App\Http\Middleware;

use App\Http\Request;

class SessionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            // secure defaults
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            
            if (env('APP_ENV') === 'production') {
                ini_set('session.cookie_secure', '1');
            }
            
            session_name('pulse_sid');
            session_start();
        }

        // clear old flash messages on new request
        if (isset($_SESSION['_flash_old'])) {
            unset($_SESSION['_flash_old']);
        }
        // move current flash to old (for one-cycle display)
        if (isset($_SESSION['_flash'])) {
            $_SESSION['_flash_old'] = $_SESSION['_flash'];
            $_SESSION['_flash'] = [];
        }

        return $next($request);
    }
}