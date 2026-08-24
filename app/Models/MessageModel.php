<?php

namespace App\Models;

use PDO;

class MessageModel extends BaseModel
{
    protected string $table = 'messages';
    protected string $primaryKey = 'id';
    protected array $fillable = ['sender_id', 'receiver_id', 'subject', 'body'];

    /**
     * Get a conversation between two users (ordered oldest → newest for chat display).
     */
    public function getConversation(int $userId, int $otherId, int $page = 1, int $perPage = 100): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            // ASC order so messages display oldest at top, newest at bottom (like a chat)
            $sql = "SELECT m.*,
                           u_s.first_name as sender_first_name, u_s.last_name as sender_last_name, u_s.avatar as sender_avatar,
                           u_r.first_name as receiver_first_name, u_r.last_name as receiver_last_name, u_r.avatar as receiver_avatar
                    FROM messages m
                    JOIN users u_s ON m.sender_id = u_s.id
                    JOIN users u_r ON m.receiver_id = u_r.id
                    WHERE ((m.sender_id = :user1 AND m.receiver_id = :user2) OR (m.sender_id = :user2 AND m.receiver_id = :user1))
                      AND m.deleted_by_sender = 0 AND m.deleted_by_receiver = 0
                    ORDER BY m.created_at ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':user2', $otherId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get the inbox — a list of UNIQUE conversation partners.
     * Shows the latest message for each person the user has talked to
     * (whether the user sent or received the last message).
     *
     * Each row includes the OTHER user's info (first_name, last_name, avatar, role)
     * so the view can display the conversation partner's name, not the sender's.
     */
    public function getInbox(int $userId, int $page = 1, int $perPage = 50): array
    {
        try {
            $offset = ($page - 1) * $perPage;

            // Get the latest message for each conversation partner.
            // We use a subquery to find the max(message id) per conversation group,
            // then join back to get the full message row + the other user's info.
            $sql = "
                SELECT m.*,
                       u_other.id as other_id,
                       u_other.first_name, u_other.last_name, u_other.avatar,
                       r.name as other_role,
                       r.slug as other_role_slug,
                       CASE WHEN m.sender_id = :user_id THEN 1 ELSE 0 END as is_sent_by_me
                FROM messages m
                INNER JOIN (
                    SELECT
                        GREATEST(sender_id, receiver_id) as u1,
                        LEAST(sender_id, receiver_id) as u2,
                        MAX(id) as max_id
                    FROM messages
                    WHERE (sender_id = :user_id OR receiver_id = :user_id)
                      AND deleted_by_sender = 0 AND deleted_by_receiver = 0
                    GROUP BY GREATEST(sender_id, receiver_id), LEAST(sender_id, receiver_id)
                ) latest ON m.id = latest.max_id
                INNER JOIN users u_other ON u_other.id = CASE WHEN m.sender_id = :user_id THEN m.receiver_id ELSE m.sender_id END
                LEFT JOIN roles r ON u_other.role_id = r.id
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            // Count total unique conversations
            $cntSql = "
                SELECT COUNT(*) FROM (
                    SELECT GREATEST(sender_id, receiver_id) as u1, LEAST(sender_id, receiver_id) as u2
                    FROM messages
                    WHERE (sender_id = ? OR receiver_id = ?)
                      AND deleted_by_sender = 0 AND deleted_by_receiver = 0
                    GROUP BY GREATEST(sender_id, receiver_id), LEAST(sender_id, receiver_id)
                ) conv
            ";
            $cntStmt = $this->db->prepare($cntSql);
            $cntStmt->execute([$userId, $userId]);
            $total = (int) $cntStmt->fetchColumn();

            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function getUnreadCount(int $userId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND read_at IS NULL AND deleted_by_receiver = 0");
            $stmt->execute([$userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function markAsRead(int $messageId, int $userId): void
    {
        try {
            $this->db->prepare("UPDATE messages SET read_at = NOW() WHERE id = ? AND receiver_id = ?")->execute([$messageId, $userId]);
        } catch (\Throwable $e) {
            return;
        }
    }

    public function markAllRead(int $userId): void
    {
        try {
            $this->db->prepare("UPDATE messages SET read_at = NOW() WHERE receiver_id = ? AND read_at IS NULL")->execute([$userId]);
        } catch (\Throwable $e) {
            return;
        }
    }
}
