<?php
/**
 * Contact Messages.
 * People reaching out. I save every one.
 */
namespace App\Models;

use App\Core\Model;
use App\Core\Collection;

class ContactMessage extends Model
{
    protected string $table = 'contact_messages';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name', 'email', 'subject', 'message', 'ip_address', 'is_read'];
    protected array $dates = ['created_at'];

    public static function unread(): Collection
    {
        return self::query(
            "SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC"
        );
    }

    public static function markRead(int $id): bool
    {
        $self = new static();
        $stmt = $self->db()->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function markUnread(int $id): bool
    {
        $self = new static();
        $stmt = $self->db()->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
