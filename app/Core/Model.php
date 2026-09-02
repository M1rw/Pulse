<?php
/**
 * Base Model.
 * 
 * Active Record pattern with a PDO backend. Ergonomic, robust,
 * supporting ArrayAccess, property access, and fluid query operations.
 */
namespace App\Core;

use App\Core\Application;
use App\Core\Collection;

class Model implements \ArrayAccess, \JsonSerializable
{
    protected \PDO $db;
    protected static ?\PDO $sharedDb = null;

    /** override in child: the table name */
    protected string $table;

    /** override in child: primary key (default: id) */
    protected string $primaryKey = 'id';

    /** the model's attributes */
    protected array $attributes = [];

    /** attributes that are fillable via create/update */
    protected array $fillable = [];

    /** dates that should be auto-parsed */
    protected array $dates = ['created_at', 'updated_at'];

    // ── constructor ──────────────────────────────────────────────

    public function __construct(array $attrs = [])
    {
        $this->db = static::getConnection();
        $this->fill($attrs);
    }

    public static function getConnection(): \PDO
    {
        if (self::$sharedDb === null) {
            $app = Application::getInstance();
            if ($app) {
                try {
                    self::$sharedDb = $app->make('db');
                } catch (\Throwable) {
                    self::$sharedDb = null;
                }
            }
            if (self::$sharedDb === null) {
                $dbPath = env('DB_DATABASE');
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

                self::$sharedDb = new \PDO("sqlite:{$dbPath}");
                self::$sharedDb->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$sharedDb->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                try {
                    self::$sharedDb->exec('PRAGMA journal_mode=WAL');
                } catch (\Throwable $e) {
                    self::$sharedDb->exec('PRAGMA journal_mode=DELETE');
                }
                self::$sharedDb->exec('PRAGMA foreign_keys=ON');

                if ($needsInit) {
                    $appInstance = new Application();
                    $appInstance->initSqliteDatabase(self::$sharedDb);
                }
            }
        }
        return self::$sharedDb;
    }

    // ── static query builders ────────────────────────────────────

    public static function all(): Collection
    {
        $self = new static();
        $stmt = $self->db->query("SELECT * FROM {$self->table}");
        $rows = $stmt->fetchAll();
        return new Collection(array_map(fn($row) => new static($row), $rows));
    }

    public static function find(int $id): ?static
    {
        $self = new static();
        $stmt = $self->db->prepare("SELECT * FROM {$self->table} WHERE {$self->primaryKey} = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new static($row) : null;
    }

    public static function where(string $column, mixed $value): Collection
    {
        $self = new static();
        $stmt = $self->db->prepare("SELECT * FROM {$self->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        $rows = $stmt->fetchAll();
        return new Collection(array_map(fn($row) => new static($row), $rows));
    }

    public static function firstWhere(string $column, mixed $value): ?static
    {
        $self = new static();
        $stmt = $self->db->prepare("SELECT * FROM {$self->table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ? new static($row) : null;
    }

    public static function query(string $sql, array $params = []): Collection
    {
        $self = new static();
        $stmt = $self->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return new Collection(array_map(fn($row) => new static($row), $rows));
    }

    public static function count(): int
    {
        $self = new static();
        $stmt = $self->db->query("SELECT COUNT(*) as total FROM {$self->table}");
        $res = $stmt->fetch();
        return (int) ($res['total'] ?? 0);
    }

    public static function latest(int $limit = 10): Collection
    {
        $self = new static();
        $stmt = $self->db->prepare("SELECT * FROM {$self->table} ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        $rows = $stmt->fetchAll();
        return new Collection(array_map(fn($row) => new static($row), $rows));
    }

    // ── instance methods ──────────────────────────────────────────

    public function save(): bool
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->update();
        }
        return $this->insert();
    }

    public function delete(): bool
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        if (!$id) return false;

        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    // ── attribute access ─────────────────────────────────────────

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    // ── ArrayAccess Implementation ───────────────────────────────

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    // ── internals ─────────────────────────────────────────────────

    public function fill(array $attrs): void
    {
        if (!empty($this->fillable)) {
            $filtered = array_intersect_key($attrs, array_flip($this->fillable));
            $this->attributes = array_merge($this->attributes, $filtered);
        } else {
            $this->attributes = array_merge($this->attributes, $attrs);
        }
        // Always preserve primary key if provided
        if (isset($attrs[$this->primaryKey])) {
            $this->attributes[$this->primaryKey] = $attrs[$this->primaryKey];
        }
        // Preserve timestamp fields if provided
        foreach ($this->dates as $dateCol) {
            if (isset($attrs[$dateCol])) {
                $this->attributes[$dateCol] = $attrs[$dateCol];
            }
        }
    }

    private function insert(): bool
    {
        $now = date('Y-m-d H:i:s');
        if (in_array('created_at', $this->dates) && !isset($this->attributes['created_at'])) {
            $this->attributes['created_at'] = $now;
        }
        if (in_array('updated_at', $this->dates) && !isset($this->attributes['updated_at'])) {
            $this->attributes['updated_at'] = $now;
        }

        $filtered = $this->fillable
            ? array_intersect_key($this->attributes, array_flip($this->fillable))
            : $this->attributes;

        if (in_array('created_at', $this->dates) && isset($this->attributes['created_at'])) {
            $filtered['created_at'] = $this->attributes['created_at'];
        }
        if (in_array('updated_at', $this->dates) && isset($this->attributes['updated_at'])) {
            $filtered['updated_at'] = $this->attributes['updated_at'];
        }

        $cols = implode(', ', array_keys($filtered));
        $placeholders = implode(', ', array_fill(0, count($filtered), '?'));

        $sql = "INSERT INTO {$this->table} ({$cols}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_values($filtered));

        if ($result) {
            $this->attributes[$this->primaryKey] = (int) $this->db->lastInsertId();
        }

        return $result;
    }

    private function update(): bool
    {
        if (in_array('updated_at', $this->dates)) {
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        }

        $filtered = $this->fillable
            ? array_intersect_key($this->attributes, array_flip($this->fillable))
            : $this->attributes;

        unset($filtered[$this->primaryKey]);
        if (in_array('updated_at', $this->dates)) {
            $filtered['updated_at'] = $this->attributes['updated_at'];
        }

        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($filtered)));
        $sql = "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?";

        $params = array_values($filtered);
        $params[] = $this->attributes[$this->primaryKey];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // ── raw PDO access ───────────────────────────────────────────

    public function db(): \PDO
    {
        return $this->db;
    }
}