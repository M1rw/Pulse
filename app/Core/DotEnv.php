<?php
/**
 * Dead-simple .env parser.
 * 
 * No regex theater. No bloated package needed for this.
 * Just read lines, parse key=value, done.
 */
namespace App\Core;

class DotEnv
{
    public function __construct(
        private readonly string $path
    ) {}

    public function load(): void
    {
        if (!file_exists($this->path) || !is_readable($this->path)) {
            return;
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // skip comments
            if (str_starts_with(trim($line), '#')) continue;

            // skip lines without an equals sign
            if (!str_contains($line, '=')) continue;

            // split on first = only (values can contain =)
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // strip quotes if present
            if (preg_match('/^("|\')(.*)\1$/', $value, $m)) {
                $value = $m[2];
            }

            // don't override existing env vars (system > file)
            if (!isset($_ENV[$key]) && getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
}
