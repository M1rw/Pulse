<?php
/**
 * Database config.
 * SQLite by default - zero config, zero dependencies.
 * Swap to MySQL by changing .env vars.
 */

return [
    'driver'   => env('DB_DRIVER', 'sqlite'),
    'host'     => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', PULSE_STORAGE . '/pulse.db'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'port'     => env('DB_PORT', 3306),
];
