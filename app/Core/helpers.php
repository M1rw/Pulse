<?php
/**
 * Global helpers.
 * 
 * Yeah I know, "global functions are bad". But these make
 * the code read like English and I'm okay with that tradeoff.
 * Every framework has 'em. Laravel has tons. Deal with it.
 */

if (!function_exists('env')) {
    /** grab from environment, with fallback */
    function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false) return $default;
        // handle "true", "false", "null" strings
        switch (strtolower($val)) {
            case 'true':  return true;
            case 'false': return false;
            case 'null':  return null;
            case 'empty': return '';
        }
        return $val;
    }
}

if (!function_exists('config')) {
    /** pull a config value with dot notation, e.g. config('app.name') */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = [];
        
        if (empty($config)) {
            $files = glob(PULSE_CONFIG . '/*.php');
            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $config[$name] = require $file;
            }
        }
        
        $parts = explode('.', $key);
        $val = $config;
        
        foreach ($parts as $segment) {
            if (!is_array($val) || !array_key_exists($segment, $val)) {
                return $default;
            }
            $val = $val[$segment];
        }
        
        return $val;
    }
}

if (!function_exists('view')) {
    /** render a view template */
    function view(string $template, array $data = []): string
    {
        $engine = \App\Core\ViewEngine::getInstance();
        return $engine->render($template, $data);
    }
}

if (!function_exists('redirect')) {
    /** send a redirect response */
    function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('e')) {
    /** escape HTML - short name because I use it everywhere */
    function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /** dump and die - the MVP of debugging */
    function dd(mixed ...$vars): never
    {
        echo '<pre style="background:#1a1a2e;color:#e94560;padding:16px;border-radius:8px;font-family:monospace;font-size:13px;margin:16px">';
        foreach ($vars as $v) {
            var_dump($v);
            echo "\n---\n";
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('asset')) {
    /** generate an asset URL path */
    function asset(string $path): string
    {
        $manifestPath = PULSE_PUBLIC . '/assets/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest[$path])) {
                return '/assets/' . $manifest[$path];
            }
        }
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    /** generate or retrieve CSRF token */
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /** echo a hidden CSRF input field */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('flash')) {
    /** set or get a flash message */
    function flash(string $key, mixed $message = null): mixed
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        // read from _flash_old (middleware moves _flash -> _flash_old each request)
        $val = $_SESSION['_flash_old'][$key] ?? null;
        if ($val !== null) unset($_SESSION['_flash_old'][$key]);
        return $val;
    }
}

if (!function_exists('log_error')) {
    /** write to the error log file */
    function log_error(string $message, array $context = []): void
    {
        $logDir = PULSE_STORAGE . '/logs';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $line = "[{$time}] ERROR: {$message}";
        
        if (!empty($context)) {
            $line .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        file_put_contents(
            "{$logDir}/pulse-{$date}.log",
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}

if (!function_exists('str_slug')) {
    /** convert a string to URL-friendly slug */
    function str_slug(string $str): string
    {
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $str);
        $str = preg_replace('/[^a-z0-9]+/i', '-', $str);
        return trim($str, '-') ?: 'untitled';
    }
}

if (!function_exists('time_ago')) {
    /** human-readable relative time */
    function time_ago(string $datetime): string
    {
        $now  = new \DateTime();
        $past = new \DateTime($datetime);
        $diff = $now->getTimestamp() - $past->getTimestamp();

        if ($diff < 60)    return 'just now';
        if ($diff < 3600)  return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return $past->format('M j, Y');
    }
}

if (!function_exists('class_basename')) {
    /** get just the class name without namespace */
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('collect')) {
    /** wrap an array in a Collection for fluent operations */
    function collect(array $items = []): \App\Core\Collection
    {
        return new \App\Core\Collection($items);
    }
}

if (!function_exists('app')) {
    /** resolve from service container */
    function app(?string $abstract = null): mixed
    {
        $app = \App\Core\Application::getInstance();
        if ($abstract === null) {
            return $app;
        }
        return $app?->make($abstract);
    }
}