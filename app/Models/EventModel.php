<?php
/**
 * EventModel — Career events (workshops, job fairs, webinars)
 *
 * NOTE: The events table uses status ENUM('upcoming','ongoing','completed','cancelled')
 * and has an `image` column (not image_url). This model matches the actual schema.
 */

namespace App\Models;

class EventModel extends BaseModel
{
    protected string $table = 'events';
    protected string $primaryKey = 'id';
    protected array $fillable = ['title', 'description', 'start_date', 'end_date', 'location', 'type', 'organizer_id', 'image', 'max_participants', 'status'];

    public function upcoming(int $limit = 6): array
    {
        return $this->query("
            SELECT e.*, u.first_name, u.last_name
            FROM events e
            LEFT JOIN users u ON u.id = e.organizer_id
            WHERE e.status IN ('upcoming','ongoing') AND e.start_date >= CURDATE()
            ORDER BY e.start_date ASC
            LIMIT ?
        ", [$limit]);
    }

    public function featured(int $limit = 3): array
    {
        return $this->upcoming($limit);
    }

    public function allEvents(int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($page, $perPage, "status IN ('upcoming','ongoing')", 'start_date', 'ASC');
    }

    public function register(int $eventId, int $userId): bool
    {
        // Avoid duplicate
        $exists = $this->queryOne("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?", [$eventId, $userId]);
        if ($exists) return false;
        return $this->execute("INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)", [$eventId, $userId]);
    }

    public function isRegistered(int $eventId, int $userId): bool
    {
        $r = $this->queryOne("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?", [$eventId, $userId]);
        return !empty($r);
    }

    public function registrationCount(int $eventId): int
    {
        $r = $this->queryOne("SELECT COUNT(*) as c FROM event_registrations WHERE event_id = ?", [$eventId]);
        return (int)($r['c'] ?? 0);
    }
}
