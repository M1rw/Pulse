<?php
/**
 * Activity Log.
 * 
 * I track what happens on the site. Not for analytics,
 * more like a heartbeat monitor. Shows the site is alive.
 */
namespace App\Models;

use App\Core\Model;
use App\Core\Collection;

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';
    protected string $primaryKey = 'id';
    protected array $fillable = ['event_type', 'description', 'metadata', 'ip_address'];
    protected array $dates = ['created_at'];

    public static function log(string $event, string $description, array $meta = [], string $ip = ''): void
    {
        $log = new static([
            'event_type'  => $event,
            'description' => $description,
            'metadata'    => json_encode($meta),
            'ip_address'  => $ip,
        ]);
        $log->save();
    }

    public static function recent(int $limit = 20): Collection
    {
        return self::query(
            "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public static function stats(): array
    {
        $self = new static();
        $total = $self->db()->query("SELECT COUNT(*) as c FROM activity_logs")->fetch()['c'];
        $today = $self->db()->query(
            "SELECT COUNT(*) as c FROM activity_logs WHERE date(created_at) = date('now')"
        )->fetch()['c'];
        $types = $self->db()->query(
            "SELECT event_type, COUNT(*) as count FROM activity_logs GROUP BY event_type ORDER BY count DESC"
        )->fetchAll();

        return [
            'total'     => (int) $total,
            'today'     => (int) $today,
            'by_type'   => $types,
        ];
    }

    /** parse the JSON metadata */
    public function getMeta(): array
    {
        return json_decode($this->metadata ?? '{}', true) ?: [];
    }
}
