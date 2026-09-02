<?php
/**
 * Request.
 * 
 * Thin wrapper around PHP's superglobals. I don't like accessing
 * $_POST directly in controllers - this gives me a clean API
 * and makes testing way easier (just pass a Request object).
 */
namespace App\Http;

class Request
{
    private array $queryParams = [];
    private array $body = [];
    private array $headers = [];
    private array $routeParams = [];
    private array $files = [];
    private string $method;
    private string $path;
    private string $uri;

    // ── capture from current request ──────────────────────────────

    public static function capture(): static
    {
        $req = new static();

        $req->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $req->uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $req->path   = parse_url($req->uri, PHP_URL_PATH) ?: '/';

        // allow _method override for HTML forms (PUT, DELETE)
        if ($req->method === 'POST' && isset($_POST['_method'])) {
            $req->method = strtoupper($_POST['_method']);
        }

        $req->queryParams = $_GET;
        $req->body       = $_POST;
        $req->files      = $_FILES;
        $req->routeParams = [];

        // grab headers from server vars
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $req->headers[$header] = $value;
            }
        }
        // content-type and content-length
        if (isset($_SERVER['CONTENT_TYPE']))   $req->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        if (isset($_SERVER['CONTENT_LENGTH']))  $req->headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];

        // handle JSON body
        if (str_contains($req->header('Content-Type', ''), 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $req->body = $decoded;
        }

        return $req;
    }

    // ── getters ───────────────────────────────────────────────────

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function header(string $name, string $default = ''): string
    {
        return $this->headers[$name] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->body);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function has(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->queryParams[$key]) && !isset($this->body[$key])) {
                return false;
            }
        }
        return true;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($this->header('Accept', ''), 'application/json');
    }

    // ── route params ──────────────────────────────────────────────

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function ip(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return trim($_SERVER['HTTP_X_REAL_IP']);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->header('User-Agent', '');
    }
}
