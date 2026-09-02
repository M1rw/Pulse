<?php
/**
 * Application - the heart of Pulse.
 * 
 * This is my DI container + kernel combo. I didn't want to 
 * separate them because for a project this size, that's just
 * over-engineering. One class that holds everything together.
 */
namespace App\Core;

use App\Http\Middleware\MiddlewareStack;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\SessionMiddleware;

class Application
{
    private static ?self $instance = null;

    /** holds all registered bindings */
    private array $bindings = [];
    
    /** singletons - resolved once, reused forever */
    private array $instances = [];
    
    /** aliases for shorter resolution */
    private array $aliases = [];
    
    private ?Router $router = null;
    private ?MiddlewareStack $middlewareStack = null;
    private bool $booted = false;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    // ── container operations ─────────────────────────────────────

    public function bind(string $abstract, \Closure|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, \Closure|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        // mark it so we only resolve once
        $this->aliases['_singletons'][] = $abstract;
    }

    public function instance(string $abstract, mixed $value): void
    {
        $this->instances[$abstract] = $value;
    }

    /** resolve something from the container */
    public function make(string $abstract): object
    {
        // already instantiated?
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // check aliases
        $abstract = $this->aliases[$abstract] ?? $abstract;

        // has a binding?
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];
            $object = is_string($concrete) ? $this->build($concrete) : $concrete($this);

            // cache if singleton
            if (in_array($abstract, $this->aliases['_singletons'] ?? [])) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        // try to just build it
        return $this->build($abstract);
    }

    /** auto-wire a class from its constructor */
    private function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class [{$class}] doesn't exist, can't build it.");
        }

        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("[{$class}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class;
        }

        $params = $constructor->getParameters();
        $deps   = [];

        foreach ($params as $param) {
            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $deps[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $deps[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException(
                    "Can't resolve [\${$param->getName()}] for [{$class}]"
                );
            }
        }

        return $reflector->newInstanceArgs($deps);
    }

    // ── boot sequence ────────────────────────────────────────────

    public function boot(): void
    {
        if ($this->booted) return;

        // register core services
        $this->registerCoreServices();
        
        // load routes
        $this->registerRoutes();
        
        $this->booted = true;
    }

    private function registerCoreServices(): void
    {
        // database connection
        $this->singleton('db', fn() => $this->createDbConnection());

        // router
        $this->singleton(Router::class, fn() => new Router());

        // view engine
        $this->singleton(ViewEngine::class, fn() => ViewEngine::getInstance());

        // middleware stack
        $this->singleton(MiddlewareStack::class, function () {
            $stack = new MiddlewareStack();
            $stack->add(new SessionMiddleware());
            $stack->add(new CsrfMiddleware());
            return $stack;
        });

        // request
        $this->singleton('request', fn() => \App\Http\Request::capture());
    }

    private function createDbConnection(): \PDO
    {
        $driver = env('DB_DRIVER', 'sqlite');
        $dbPath = env('DB_DATABASE');

        if ($driver === 'sqlite') {
            if (!$dbPath) {
                $dbPath = PULSE_STORAGE . '/pulse.db';
            }

            $isVercel = getenv('VERCEL') !== false || env('VERCEL') !== null;
            $storageWritable = is_writable(PULSE_STORAGE) || (!file_exists(PULSE_STORAGE) && @is_writable(dirname(PULSE_STORAGE)));

            if ($isVercel || !$storageWritable) {
                if (str_starts_with($dbPath, PULSE_STORAGE) || !@is_writable(dirname($dbPath))) {
                    $tmpDir = is_dir('/tmp') && is_writable('/tmp') ? '/tmp' : sys_get_temp_dir();
                    $dbPath = $tmpDir . '/pulse.db';
                }
            } elseif (!str_starts_with($dbPath, '/') && !preg_match('/^[A-Za-z]:[\\\\\/]/', $dbPath)) {
                $dbPath = PULSE_ROOT . '/' . $dbPath;
            }

            $dir = dirname($dbPath);
            if (!is_dir($dir) && (@is_writable($dir) || @is_writable(dirname($dir)))) {
                @mkdir($dir, 0755, true);
            }

            $needsInit = !file_exists($dbPath);

            $pdo = new \PDO("sqlite:{$dbPath}");
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            try {
                $pdo->exec('PRAGMA journal_mode=WAL');
            } catch (\Throwable $e) {
                $pdo->exec('PRAGMA journal_mode=DELETE');
            }
            $pdo->exec('PRAGMA foreign_keys=ON');

            if ($needsInit) {
                $this->initSqliteDatabase($pdo);
            }

            return $pdo;
        } else {
            $host = env('DB_HOST', '127.0.0.1');
            $name = env('DB_DATABASE', 'pulse');
            $user = env('DB_USERNAME', 'root');
            $pass = env('DB_PASSWORD', '');
            $pdo = new \PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        }

        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function initSqliteDatabase(\PDO $pdo): void
    {
        $migrations = glob(PULSE_ROOT . '/database/migrations/*.sql') ?: [];
        sort($migrations);
        foreach ($migrations as $file) {
            $sql = file_get_contents($file);
            if ($sql) {
                try {
                    $pdo->exec($sql);
                } catch (\PDOException $e) {
                    // ignore if already executed
                }
            }
        }

        $seeds = glob(PULSE_ROOT . '/database/seeds/*.sql') ?: [];
        sort($seeds);
        foreach ($seeds as $file) {
            $sql = file_get_contents($file);
            if ($sql) {
                try {
                    $pdo->exec($sql);
                } catch (\PDOException $e) {
                    // ignore seed conflicts
                }
            }
        }
    }

    private function registerRoutes(): void
    {
        $router = $this->make(Router::class);

        // web routes
        $webRoutes = PULSE_ROOT . '/routes/web.php';
        if (file_exists($webRoutes)) {
            require $webRoutes;
        }

        // api routes
        $apiRoutes = PULSE_ROOT . '/routes/api.php';
        if (file_exists($apiRoutes)) {
            require $apiRoutes;
        }
    }

    // ── handle the request ───────────────────────────────────────

    public function run(): void
    {
        $request  = $this->make('request');
        $router   = $this->make(Router::class);
        $stack    = $this->make(MiddlewareStack::class);

        try {
            // run middleware first
            $response = $stack->handle($request, function ($req) use ($router) {
                return $router->dispatch($req);
            });

            // send response
            $this->sendResponse($response);

        } catch (\App\Core\Exceptions\NotFoundException $e) {
            http_response_code(404);
            echo view('errors/404');
        } catch (\App\Core\Exceptions\CsrfException $e) {
            http_response_code(419);
            echo view('errors/419');
        }
    }

    private function sendResponse(mixed $response): void
    {
        if (is_string($response)) {
            echo $response;
        } elseif ($response instanceof \App\Http\Response) {
            $response->send();
        } else {
            echo (string) $response;
        }
    }
}
