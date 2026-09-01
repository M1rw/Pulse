<?php
/**
 * Pulse CLI.
 * 
 * My take on Artisan. Not a Tinkerwell replacement, but for
 * the operations I actually do daily: migrate, seed, serve,
 * and route listing. Each command has colorized output and
 * clear success/error feedback.
 * 
 * Usage: php cli/pulse.php <command> [options]
 */

// ── bootstrap (without the web server parts) ─────────────────
define('PULSE_ROOT', dirname(__DIR__));
define('PULSE_APP', PULSE_ROOT . '/app');
define('PULSE_STORAGE', PULSE_ROOT . '/storage');
define('PULSE_CONFIG', PULSE_ROOT . '/config');
define('PULSE_VIEWS', PULSE_ROOT . '/app/Views');

// minimal error handling for CLI
set_error_handler(function ($severity, $msg, $file, $line) {
    echo cli_color("[ERROR] {$msg} in {$file}:{$line}", 'red') . "\n";
});

// ── CLI helpers ──────────────────────────────────────────────

function cli_color(string $text, string $color = 'white'): string
{
    $codes = [
        'red'    => 31, 'green'  => 32, 'yellow' => 33,
        'blue'   => 34, 'magenta'=> 35, 'cyan'   => 36,
        'white'  => 37, 'gray'   => 90,
        'bold'   => 1,  'dim'    => 2,
    ];
    $code = $codes[$color] ?? 37;
    return "\033[{$code}m{$text}\033[0m";
}

function cli_line(string $text = ''): void
{
    echo $text . "\n";
}

function cli_success(string $msg): void
{
    echo cli_color('  ✓ ', 'green') . $msg . "\n";
}

function cli_error(string $msg): void
{
    echo cli_color('  ✗ ', 'red') . $msg . "\n";
}

function cli_info(string $msg): void
{
    echo cli_color('  → ', 'cyan') . $msg . "\n";
}

function cli_warn(string $msg): void
{
    echo cli_color('  ! ', 'yellow') . $msg . "\n";
}

function cli_header(string $title): void
{
    echo "\n";
    echo cli_color("  ╔══ {$title}", 'bold') . "\n";
    echo cli_color('  ╚' . str_repeat('═', strlen($title) + 2), 'bold') . "\n";
}

function cli_table(array $headers, array $rows): void
{
    // calculate column widths
    $widths = array_map('strlen', $headers);
    foreach ($rows as $row) {
        foreach ($row as $i => $cell) {
            $widths[$i] = max($widths[$i] ?? 0, strlen((string)$cell));
        }
    }
    
    // header
    $headerLine = '|';
    $sepLine = '+';
    foreach ($headers as $i => $h) {
        $w = $widths[$i] ?? 10;
        $headerLine .= ' ' . str_pad($h, $w) . ' |';
        $sepLine .= str_repeat('-', $w + 2) . '+';
    }
    echo $sepLine . "\n";
    echo cli_color($headerLine, 'bold') . "\n";
    echo $sepLine . "\n";
    
    // rows
    foreach ($rows as $row) {
        $line = '|';
        foreach ($row as $i => $cell) {
            $w = $widths[$i] ?? 10;
            $line .= ' ' . str_pad((string)$cell, $w) . ' |';
        }
        echo $line . "\n";
    }
    echo $sepLine . "\n";
}

// ── get the DB connection ─────────────────────────────────────

function getDb(): \PDO
{
    // load .env if exists
    $envPath = PULSE_ROOT . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
            [$key, $val] = explode('=', $line, 2);
            $val = trim($val);
            if (preg_match('/^("|")(.*)\1$/', $val, $m)) $val = $m[2];
            putenv(trim($key) . '=' . $val);
        }
    }
    
    $dbPath = getenv('DB_DATABASE') ?: PULSE_STORAGE . '/pulse.db';
    if (!str_starts_with($dbPath, '/') && !preg_match('/^[A-Za-z]:[\\\\\/]/', $dbPath)) {
        $dbPath = PULSE_ROOT . '/' . $dbPath;
    }
    
    // ensure storage exists
    if (!is_dir(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
    }
    
    $pdo = new \PDO("sqlite:{$dbPath}");
    $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA foreign_keys=ON');
    
    return $pdo;
}

// ── commands ──────────────────────────────────────────────────

function cmd_help(): void
{
    cli_header('Pulse CLI');
    echo cli_color('  A lightweight CLI for the Pulse framework.', 'gray') . "\n\n";
    echo "  Commands:\n\n";
    
    $cmds = [
        ['serve',     'Start the development server on localhost:8080'],
        ['migrate',   'Run all database migrations'],
        ['seed',      'Seed the database with sample data'],
        ['fresh',     'Drop all tables, migrate, then seed'],
        ['routes',    'List all registered routes'],
        ['stats',     'Show project and activity statistics'],
        ['cache:clear','Clear all cached files'],
        ['help',      'Show this help screen'],
    ];
    
    cli_table(['Command', 'Description'], $cmds);
    echo "\n";
}

function cmd_serve(): void
{
    $host = 'localhost';
    $port = 8080;
    
    // check if port is taken
    $sock = @fsockopen($host, $port, $errno, $errstr, 1);
    if ($sock) {
        fclose($sock);
        cli_error("Port {$port} is already in use.");
        cli_info("Try: php cli/pulse.php serve --port=8081");
        return;
    }
    
    cli_header('Pulse Dev Server');
    cli_info("Starting server at http://{$host}:{$port}");
    cli_info("Document root: " . PULSE_ROOT . '/public');
    cli_warn("Press Ctrl+C to stop");
    echo "\n";
    
    // pass through to PHP's built-in server
    passthru(escapeshellarg(PHP_BINARY) . " -S {$host}:{$port} -t " . escapeshellarg(PULSE_ROOT . '/public'));
}

function cmd_migrate(): void
{
    cli_header('Database Migration');
    
    $db = getDb();
    $files = glob(PULSE_ROOT . '/database/migrations/*.sql');
    
    if (empty($files)) {
        cli_warn('No migration files found.');
        return;
    }
    
    foreach ($files as $file) {
        $name = basename($file);
        cli_info("Running {$name}...");
        
        $sql = file_get_contents($file);
        try {
            $db->exec($sql);
            cli_success("{$name} — OK");
        } catch (\PDOException $e) {
            cli_error("{$name} — FAILED: {$e->getMessage()}");
        }
    }
    
    // verify tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll();
    echo "\n";
    cli_info(count($tables) . ' tables created:');
    foreach ($tables as $t) {
        cli_success($t['name']);
    }
}

function cmd_seed(): void
{
    cli_header('Seeding Database');
    
    $db = getDb();
    $files = glob(PULSE_ROOT . '/database/seeds/*.sql');
    
    if (empty($files)) {
        cli_warn('No seed files found.');
        return;
    }
    
    foreach ($files as $file) {
        $name = basename($file);
        cli_info("Seeding from {$name}...");
        
        $sql = file_get_contents($file);
        try {
            $db->exec($sql);
            cli_success("{$name} — OK");
        } catch (\PDOException $e) {
            // seed uses INSERT OR IGNORE so conflicts are fine
            cli_warn("{$name} — some rows skipped (already exist)");
        }
    }
    
    echo "\n";
    
    // print counts
    $counts = [
        'projects'        => $db->query('SELECT COUNT(*) as c FROM projects')->fetch()['c'],
        'activity_logs'   => $db->query('SELECT COUNT(*) as c FROM activity_logs')->fetch()['c'],
        'contact_messages'=> $db->query('SELECT COUNT(*) as c FROM contact_messages')->fetch()['c'],
    ];
    
    foreach ($counts as $table => $count) {
        cli_success("{$table}: {$count} rows");
    }
}

function cmd_fresh(): void
{
    cli_header('Fresh Database');
    cli_warn('This will DROP all tables and recreate everything.');
    echo "\n";
    
    $db = getDb();
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll();
    
    foreach ($tables as $t) {
        $db->exec("DROP TABLE IF EXISTS {$t['name']}");
        cli_info("Dropped table: {$t['name']}");
    }
    
    echo "\n";
    cmd_migrate();
    echo "\n";
    cmd_seed();
    
    cli_success("Database is fresh and seeded.");
}

function cmd_routes(): void
{
    cli_header('Registered Routes');
    
    // we need to parse the route files to extract route definitions
    // since we can't fully boot the app in CLI easily
    
    // simple regex extraction from route files
    $routeFiles = [
        PULSE_ROOT . '/routes/web.php',
        PULSE_ROOT . '/routes/api.php',
    ];
    
    $routes = [];
    foreach ($routeFiles as $file) {
        if (!file_exists($file)) continue;
        $content = file_get_contents($file);
        
        // match patterns like: ->get('/path', [...])
        preg_match_all('/->(get|post|put|delete|patch)\(\s*[\'"](\/?[\w\/-\{\}\?]*)[\'"]\s*,/i', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $m) {
            $routes[] = [
                strtoupper($m[1]),
                $m[2],
                basename(str_replace(PULSE_ROOT, '', $file)),
            ];
        }
    }
    
    if (empty($routes)) {
        cli_warn('No routes found. Make sure route files exist.');
        return;
    }
    
    cli_table(['Method', 'URI', 'File'], $routes);
    echo "\n  Total: " . count($routes) . " routes\n\n";
}

function cmd_stats(): void
{
    cli_header('Pulse Statistics');
    
    $db = getDb();
    
    $projects = $db->query('SELECT COUNT(*) as c FROM projects')->fetch()['c'];
    $published = $db->query("SELECT COUNT(*) as c FROM projects WHERE status='published'")->fetch()['c'];
    $featured = $db->query('SELECT COUNT(*) as c FROM projects WHERE featured=1')->fetch()['c'];
    $messages = $db->query('SELECT COUNT(*) as c FROM contact_messages')->fetch()['c'];
    $unread = $db->query('SELECT COUNT(*) as c FROM contact_messages WHERE is_read=0')->fetch()['c'];
    $activity = $db->query('SELECT COUNT(*) as c FROM activity_logs')->fetch()['c'];
    $today = $db->query("SELECT COUNT(*) as c FROM activity_logs WHERE date(created_at)=date('now')")->fetch()['c'];
    
    cli_table(['Metric', 'Count'], [
        ['Total Projects',     $projects],
        ['Published',          $published],
        ['Featured',           $featured],
        ['Contact Messages',   $messages],
        ['Unread Messages',    $unread],
        ['Total Activity',     $activity],
        ['Activity (Today)',   $today],
        ['PHP Version',        PHP_VERSION],
        ['DB Size',            round(filesize(getenv('DB_DATABASE') ?: PULSE_STORAGE . '/pulse.db') / 1024, 1) . ' KB'],
    ]);
    echo "\n";
}

function cmd_cache_clear(): void
{
    cli_header('Clearing Cache');
    
    $dirs = [
        PULSE_STORAGE . '/cache',
        PULSE_STORAGE . '/views',
    ];
    
    $cleared = 0;
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        $files = glob($dir . '/*');
        foreach ($files as $f) {
            if (is_file($f)) {
                unlink($f);
                $cleared++;
            }
        }
    }
    
    cli_success("Cleared {$cleared} cached files.");
    echo "\n";
}

// ── main dispatch ─────────────────────────────────────────────

$command = $argv[1] ?? 'help';

echo cli_color('\n  ⚡ Pulse CLI', 'bold') . "\n";

// route the command
match ($command) {
    'help'         => cmd_help(),
    'serve'        => cmd_serve(),
    'migrate'      => cmd_migrate(),
    'seed'         => cmd_seed(),
    'fresh'        => cmd_fresh(),
    'routes'       => cmd_routes(),
    'stats'        => cmd_stats(),
    'cache:clear'  => cmd_cache_clear(),
    default        => [
        cli_error("Unknown command: {$command}"),
        cli_info("Run 'php cli/pulse.php help' for available commands."),
    ],
};
