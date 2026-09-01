<?php
/**
 * Router.
 * 
 * I wrote this from scratch because I wanted regex-based routes
 * with named captures, optional params, and middleware per route.
 * It's not the fastest router out there, but it reads beautifully.
 */
namespace App\Core;

use App\Http\Request;

class Router
{
    /** @var array<string, array{method:string, pattern:string, handler:mixed, middleware:array, name:?string}> */
    private array $routes = [];

    /** registered route names -> URIs for url generation */
    private array $namedRoutes = [];

    /** current route group prefix */
    private array $groupStack = [];

    /** current group middleware */
    private array $groupMiddleware = [];

    // ── registration methods ─────────────────────────────────────

    public function get(string $uri, \Closure|array|string $handler): self
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, \Closure|array|string $handler): self
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, \Closure|array|string $handler): self
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function delete(string $uri, \Closure|array|string $handler): self
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function patch(string $uri, \Closure|array|string $handler): self
    {
        return $this->addRoute('PATCH', $uri, $handler);
    }

    /** register a route with any method */
    public function addRoute(string $method, string $uri, \Closure|array|string $handler): self
    {
        $prefix = implode('', $this->groupStack);
        $uri = rtrim($prefix . $uri, '/');
        if ($uri === '') $uri = '/';

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $uri,
            'handler'    => $handler,
            'middleware' => [...$this->groupMiddleware],
            'name'       => null,
        ];

        return $this;
    }

    /** name a route for URL generation later */
    public function name(string $name): self
    {
        $index = array_key_last($this->routes);
        $this->routes[$index]['name'] = $name;
        $this->namedRoutes[$name] = $this->routes[$index]['pattern'];
        return $this;
    }

    /** route grouping with prefix and optional middleware */
    public function group(array $attributes, \Closure $callback): void
    {
        $prevPrefix = $this->groupStack;
        $prevMid    = $this->groupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->groupStack[] = '/' . trim($attributes['prefix'], '/');
        }
        if (isset($attributes['middleware'])) {
            $mid = (array) $attributes['middleware'];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $mid);
        }

        $callback($this);

        $this->groupStack     = $prevPrefix;
        $this->groupMiddleware = $prevMid;
    }

    /** generate a URL from a named route */
    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route [{$name}] not found.");
        }

        $uri = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $uri = str_replace("{{$key}}", $value, $uri);
            $uri = str_replace("{{$key}?}", $value, $uri);
        }
        // clean up optional params that weren't provided
        $uri = preg_replace('/\/\{\w+\?\}/', '', $uri);
        return $uri;
    }

    // ── dispatch ─────────────────────────────────────────────────

    public function dispatch(Request $request): mixed
    {
        $method = $request->method();
        $path   = '/' . trim($request->path(), '/');
        if ($path !== '/') $path = rtrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $regex = $this->buildPattern($route['pattern']);

            if (preg_match($regex, $path, $matches)) {
                // pull out named params (numeric keys from preg_match)
                $params = array_filter(
                    $matches,
                    fn($k) => !is_int($k),
                    ARRAY_FILTER_USE_KEY
                );

                $request->setRouteParams($params);

                return $this->resolveHandler($route['handler'], $params);
            }
        }

        throw new Exceptions\NotFoundException("No route for [{$method} {$path}]");
    }

    /** convert route pattern to regex */
    private function buildPattern(string $pattern): string
    {
        // escape forward slashes
        $regex = preg_quote($pattern, '~');

        // convert {param} to named capture groups
        $regex = preg_replace('/\\\{(\w+)\\\}/', '(?P<$1>[^/]+)', $regex);

        // convert {param?} to optional named captures
        $regex = preg_replace('/\\\{(\w+)\\\?\\\}/', '(?:/(?P<$1>[^/]+))?', $regex);

        return '~^' . $regex . '$~';
    }

    /** turn the handler into actual executable code */
    private function resolveHandler(\Closure|array|string $handler, array $params): mixed
    {
        if ($handler instanceof \Closure) {
            return $handler(...array_values($params));
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler, 2);
            $instance = new $controller();
            return $instance->$method(...array_values($params));
        }

        if (is_array($handler)) {
            [$controller, $method] = $handler;
            $instance = is_string($controller) ? new $controller() : $controller;
            return $instance->$method(...array_values($params));
        }

        // it's a callable
        if (is_callable($handler)) {
            return $handler(...array_values($params));
        }

        throw new \RuntimeException('Invalid route handler.');
    }
}