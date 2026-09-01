<?php
/**
 * Response.
 * 
 * I return these from controllers when I need more control
 * than just echoing a string. JSON APIs especially.
 */
namespace App\Http;

class Response
{
    private int $status;
    private array $headers;
    private string $body;

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->status  = $status;
        $this->headers = $headers;
        $this->body    = $body;
    }

    // ── static factories ──────────────────────────────────────────

    public static function html(string $html, int $status = 200): static
    {
        return new static($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function json(mixed $data, int $status = 200): static
    {
        return new static(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    public static function redirect(string $url, int $status = 302): static
    {
        return new static('', $status, ['Location' => $url]);
    }

    public static function download(string $filePath, string $fileName = ''): static
    {
        if (!file_exists($filePath)) {
            return self::json(['error' => 'File not found'], 404);
        }
        $name = $fileName ?: basename($filePath);
        return new static(
            file_get_contents($filePath),
            200,
            [
                'Content-Type'        => mime_content_type($filePath),
                'Content-Disposition' => 'attachment; filename="' . $name . '"',
                'Content-Length'      => filesize($filePath),
            ]
        );
    }

    // ── chainable setters ─────────────────────────────────────────

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    // ── send ──────────────────────────────────────────────────────

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }
}