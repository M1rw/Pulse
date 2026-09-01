<?php
/**
 * Middleware Stack.
 * 
 * Onion-style middleware. Each layer wraps the next.
 * Request goes in, gets processed layer by layer, hits the
 * core handler, then the response bubbles back out.
 */
namespace App\Http\Middleware;

use App\Http\Request;

class MiddlewareStack
{
    private array $middlewares = [];

    public function add(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Build the onion. Each middleware wraps the next one.
     * The innermost is the actual route handler.
     */
    public function handle(Request $request, \Closure $core): mixed
    {
        // start from the last middleware, wrap backwards
        $next = $core;

        for ($i = count($this->middlewares) - 1; $i >= 0; $i--) {
            $next = fn(Request $req) => $this->middlewares[$i]->handle($req, $next);
        }

        return $next($request);
    }
}