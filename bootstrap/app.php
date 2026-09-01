<?php
/**
 * Bootstrap - where Pulse wakes up.
 * 
 * Lean bootstrap with container and PSR-4 autoloader.
 */

use App\Core\Application;
use App\Core\DotEnv;

// ── timing ─────────────────────────────────────────────────────
$startTime = hrtime(true);

// ── paths ──────────────────────────────────────────────────────
define('PULSE_ROOT', dirname(__DIR__));
define('PULSE_APP', PULSE_ROOT . '/app');
define('PULSE_PUBLIC', PULSE_ROOT . '/public');
define('PULSE_STORAGE', PULSE_ROOT . '/storage');
define('PULSE_CONFIG', PULSE_ROOT . '/config');
define('PULSE_VIEWS', PULSE_ROOT . '/app/Views');

// ── autoloader (PSR-4 hand-rolled) ────────────────────────────
spl_autoload_register(function (string $class) {
    if (str_starts_with($class, 'App\\')) {
        $relativeClass = substr($class, strlen('App\\'));
        $file = PULSE_APP . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
        return;
    }

    if (str_starts_with($class, 'Database\\')) {
        $relativeClass = substr($class, strlen('Database\\'));
        $file = PULSE_ROOT . '/database/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
        return;
    }
});

// ── global helpers ─────────────────────────────────────────────
require_once PULSE_APP . '/Core/helpers.php';

// ── load env ───────────────────────────────────────────────────
$envPath = PULSE_ROOT . '/.env';
if (file_exists($envPath)) {
    (new DotEnv($envPath))->load();
} else {
    // first run? we'll use defaults
    putenv('APP_ENV=development');
    putenv('APP_DEBUG=true');
    putenv('DB_DRIVER=sqlite');
    putenv('DB_DATABASE=' . PULSE_STORAGE . '/pulse.db');
}

// ── error handling - fail loud in dev, quiet in prod ───────────
error_reporting(E_ALL);
ini_set('display_errors', env('APP_DEBUG', true) ? '1' : '0');

// ── whoops handler ─────────────────────────────────────────────
set_exception_handler(function (\Throwable $e) {
    if (env('APP_DEBUG', true)) {
        echo '<div style="background:#1a1a2e;color:#e94560;padding:24px;font-family:monospace;max-width:900px;margin:40px auto;border-radius:8px;border-left:4px solid #e94560">';
        echo '<h3 style="margin:0 0 12px">☠ ' . htmlspecialchars(get_class($e)) . '</h3>';
        echo '<p style="margin:0 0 8px;color:#eee">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre style="color:#888;font-size:12px;overflow-x:auto">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        http_response_code(500);
        if (file_exists(PULSE_VIEWS . '/errors/500.php')) {
            include PULSE_VIEWS . '/errors/500.php';
        } else {
            echo '500 Server Error';
        }
    }
    // always log it
    if (function_exists('log_error')) {
        log_error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    }
});

// ── ignite ─────────────────────────────────────────────────────
$app = new Application();
$app->boot();

// ── store start time for profiling ─────────────────────────────
$app->instance('start_time', $startTime);

return $app;
