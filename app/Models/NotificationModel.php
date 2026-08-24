<?php

namespace App\Models;

use PDO;

class NotificationModel extends BaseModel
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id', 'type', 'title', 'message', 'data'];

    public function getForUser(int $userId, int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getUnread(int $userId): array
    {
        try {
            return $this->query("SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC", [$userId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getUnreadCount(int $userId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
            $stmt->execute([$userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function markAsRead(int $id, int $userId): void
    {
        try {
            $this->db->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
        } catch (\Throwable $e) {
            return;
        }
    }

    public function markAllRead(int $userId): void
    {
        try {
            $this->db->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL")->execute([$userId]);
        } catch (\Throwable $e) {
            return;
        }
    }

    public function createNotification(int $userId, string $type, string $title, string $message, ?array $data = null): int
    {
        try {
            $sql = "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)";
            $json = $data ? json_encode($data) : null;
            $this->db->prepare($sql)->execute([$userId, $type, $title, $message, $json]);
            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getRecentForUser(int $userId, int $limit = 5): array
    {
        try {
            $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
