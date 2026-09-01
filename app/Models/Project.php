<?php
/**
 * Project Model.
 * 
 * This is the star of the show. The portfolio projects
 * that prove I can actually ship things.
 */
namespace App\Models;

use App\Core\Model;
use App\Core\Collection;

class Project extends Model
{
    protected string $table = 'projects';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'title', 'slug', 'description', 'long_description',
        'tech_stack', 'category', 'thumbnail', 'live_url',
        'source_url', 'featured', 'sort_order', 'status'
    ];

    // ── scopes (they call them scopes in Laravel, I like the name) ──

    public static function featured(): Collection
    {
        return self::query(
            "SELECT * FROM projects WHERE featured = 1 AND status = 'published' ORDER BY sort_order ASC, created_at DESC"
        );
    }

    public static function published(): Collection
    {
        return self::query(
            "SELECT * FROM projects WHERE status = 'published' ORDER BY created_at DESC"
        );
    }

    public static function byCategory(string $category): Collection
    {
        return self::query(
            "SELECT * FROM projects WHERE category = ? AND status = 'published' ORDER BY created_at DESC",
            [$category]
        );
    }

    public static function categories(): array
    {
        $self = new static();
        $rows = $self->db()->query("SELECT DISTINCT category FROM projects WHERE status = 'published'")->fetchAll();
        return array_column($rows, 'category');
    }

    public static function search(string $q): Collection
    {
        $self = new static();
        $like = "%{$q}%";
        $stmt = $self->db()->prepare(
            "SELECT * FROM projects WHERE status = 'published' AND (title LIKE ? OR description LIKE ? OR tech_stack LIKE ?) ORDER BY created_at DESC"
        );
        $stmt->execute([$like, $like, $like]);
        $rows = $stmt->fetchAll();
        return new Collection(array_map(fn($r) => new static($r), $rows));
    }

    // ── helpers ────────────────────────────────────────────────────

    /** get tech stack as an array */
    public function techList(): array
    {
        return array_filter(array_map('trim', explode(',', $this->tech_stack ?? '')));
    }

    /** generate an excerpt from long_description */
    public function excerpt(int $len = 120): string
    {
        $text = $this->long_description ?: $this->description;
        if (strlen($text) <= $len) return $text;
        return substr($text, 0, $len) . '...';
    }
}