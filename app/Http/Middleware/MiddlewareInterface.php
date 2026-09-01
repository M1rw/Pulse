<?php
/**
 * Contract for middleware.
 * Keep it minimal. Handle the request, pass it deeper.
 */
namespace App\Http\Middleware;

use App\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): mixed;
}