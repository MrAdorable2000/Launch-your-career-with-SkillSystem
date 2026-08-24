<?php
/**
 * ForumModel — Discussion forum (uses existing `discussions` + `comments` tables).
 */

namespace App\Models;

class ForumModel extends BaseModel
{
    protected string $table = 'discussions';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id', 'title', 'body', 'category', 'tags', 'views_count'];

    public function recent(int $limit = 10): array
    {
        return $this->query("
            SELECT d.*, u.first_name, u.last_name, u.avatar,
                   (SELECT COUNT(*) FROM comments c WHERE c.discussion_id = d.id) as comment_count
            FROM discussions d
            JOIN users u ON u.id = d.user_id
            ORDER BY d.created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    public function byCategory(string $category, int $limit = 20): array
    {
        return $this->query("
            SELECT d.*, u.first_name, u.last_name, u.avatar,
                   (SELECT COUNT(*) FROM comments c WHERE c.discussion_id = d.id) as comment_count
            FROM discussions d
            JOIN users u ON u.id = d.user_id
            WHERE d.category = ?
            ORDER BY d.created_at DESC
            LIMIT ?
        ", [$category, $limit]);
    }

    public function find(int $id): ?array
    {
        return $this->queryOne("
            SELECT d.*, u.first_name, u.last_name, u.avatar, u.role_id
            FROM discussions d
            JOIN users u ON u.id = d.user_id
            WHERE d.id = ?
        ", [$id]);
    }

    public function comments(int $discussionId): array
    {
        return $this->query("
            SELECT c.*, u.first_name, u.last_name, u.avatar, u.role_id
            FROM comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.discussion_id = ?
            ORDER BY c.created_at ASC
        ", [$discussionId]);
    }

    public function addComment(int $discussionId, int $userId, string $body): bool
    {
        return $this->execute("INSERT INTO comments (discussion_id, user_id, body) VALUES (?, ?, ?)", [$discussionId, $userId, $body]);
    }

    public function incrementViews(int $id): void
    {
        $this->execute("UPDATE discussions SET views_count = views_count + 1 WHERE id = ?", [$id]);
    }

    public function categories(): array
    {
        return $this->query("SELECT DISTINCT category FROM discussions WHERE category IS NOT NULL AND category != '' ORDER BY category");
    }
}
