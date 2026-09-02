<?php
/**
 * Collection.
 * 
 * Fluid array wrapper supporting mapping, filtering, reducing,
 * ArrayAccess, JsonSerializable, IteratorAggregate, and Countable.
 */
namespace App\Core;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    private array $items;

    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    // ── core operations ──────────────────────────────────────────

    public function map(\Closure $callback): static
    {
        $results = [];
        foreach ($this->items as $key => $item) {
            $results[$key] = $callback($item, $key);
        }
        return new static($results);
    }

    public function filter(?\Closure $callback = null): static
    {
        if ($callback === null) {
            return new static(array_values(array_filter($this->items)));
        }
        $results = [];
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key)) {
                $results[] = $item;
            }
        }
        return new static($results);
    }

    public function each(\Closure $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    public function reduce(\Closure $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function flatMap(\Closure $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            $mapped = $callback($item, $key);
            if (is_array($mapped) || $mapped instanceof self) {
                foreach ($mapped as $sub) {
                    $result[] = $sub;
                }
            } else {
                $result[] = $mapped;
            }
        }
        return new static($result);
    }

    public function pluck(string $key): static
    {
        return $this->map(fn($item) => is_array($item) || $item instanceof \ArrayAccess ? ($item[$key] ?? null) : ($item->$key ?? null));
    }

    public function where(string $key, mixed $value): static
    {
        return $this->filter(fn($item) => 
            (is_array($item) || $item instanceof \ArrayAccess ? ($item[$key] ?? null) : ($item->$key ?? null)) === $value
        );
    }

    public function first(?\Closure $callback = null): mixed
    {
        if ($callback) {
            foreach ($this->items as $key => $item) {
                if ($callback($item, $key)) return $item;
            }
            return null;
        }
        return $this->items[0] ?? null;
    }

    public function last(?\Closure $callback = null): mixed
    {
        if ($callback) {
            foreach (array_reverse($this->items) as $key => $item) {
                if ($callback($item, $key)) return $item;
            }
            return null;
        }
        $count = count($this->items);
        return $count > 0 ? $this->items[$count - 1] : null;
    }

    public function take(int $limit): static
    {
        return new static(array_slice($this->items, 0, $limit));
    }

    public function skip(int $count): static
    {
        return new static(array_slice($this->items, $count));
    }

    public function slice(int $offset, ?int $length = null): static
    {
        return new static(array_slice($this->items, $offset, $length));
    }

    public function unique(string|null $key = null): static
    {
        if ($key === null) {
            return new static(array_values(array_unique($this->items, SORT_REGULAR)));
        }
        $seen = [];
        return $this->filter(function ($item) use ($key, &$seen) {
            $val = is_array($item) || $item instanceof \ArrayAccess ? ($item[$key] ?? null) : ($item->$key ?? null);
            if (in_array($val, $seen, true)) return false;
            $seen[] = $val;
            return true;
        });
    }

    public function sortBy(string $key, bool $desc = false): static
    {
        $items = $this->items;
        usort($items, function ($a, $b) use ($key) {
            $valA = is_array($a) || $a instanceof \ArrayAccess ? ($a[$key] ?? null) : ($a->$key ?? null);
            $valB = is_array($b) || $b instanceof \ArrayAccess ? ($b[$key] ?? null) : ($b->$key ?? null);
            return $valA <=> $valB;
        });
        if ($desc) $items = array_reverse($items);
        return new static($items);
    }

    public function reverse(): static
    {
        return new static(array_reverse($this->items));
    }

    public function values(): static
    {
        return new static(array_values($this->items));
    }

    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    public function merge(array|Collection $items): static
    {
        $arr = $items instanceof static ? $items->all() : $items;
        return new static(array_merge($this->items, $arr));
    }

    public function groupBy(string|\Closure $key): static
    {
        $groups = [];
        foreach ($this->items as $item) {
            $groupKey = is_string($key) 
                ? (is_array($item) || $item instanceof \ArrayAccess ? ($item[$key] ?? 'null') : ($item->$key ?? 'null'))
                : $key($item);
            $groups[$groupKey][] = $item;
        }
        return new static($groups);
    }

    public function countBy(string|\Closure $key): static
    {
        $counts = [];
        foreach ($this->items as $item) {
            $k = is_string($key) 
                ? (is_array($item) || $item instanceof \ArrayAccess ? ($item[$key] ?? 'null') : ($item->$key ?? 'null'))
                : $key($item);
            $counts[$k] = ($counts[$k] ?? 0) + 1;
        }
        return new static($counts);
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->items);
    }

    public function join(string $glue): string
    {
        return $this->implode($glue);
    }

    // ── checks ───────────────────────────────────────────────────

    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    // ── output ───────────────────────────────────────────────────

    public function all(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return array_map(function ($item) {
            if ($item instanceof Model) return $item->toArray();
            if ($item instanceof self) return $item->toArray();
            if (is_object($item) && method_exists($item, 'toArray')) return $item->toArray();
            return (array) $item;
        }, $this->items);
    }

    public function toJson(int $flags = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $flags);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function get(int|string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    // ── interfaces ───────────────────────────────────────────────

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    // ── ArrayAccess ──────────────────────────────────────────────

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    // ── pipe / tap for method chaining ───────────────────────────

    public function tap(\Closure $callback): static
    {
        $callback($this);
        return $this;
    }

    public function pipe(\Closure $callback): mixed
    {
        return $callback($this);
    }
}
