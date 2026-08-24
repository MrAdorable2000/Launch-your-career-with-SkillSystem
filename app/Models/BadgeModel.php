<?php
/**
 * BadgeModel — Achievement badges for students.
 */

namespace App\Models;

class BadgeModel extends BaseModel
{
    protected string $table = 'badges';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name', 'slug', 'description', 'icon', 'color', 'criteria', 'points'];

    public function getAll(): array
    {
        return $this->query("SELECT * FROM badges ORDER BY points ASC");
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne("SELECT * FROM badges WHERE slug = ?", [$slug]);
    }

    public function getForStudent(int $studentId): array
    {
        return $this->query("
            SELECT b.*, sb.awarded_at
            FROM student_badges sb
            JOIN badges b ON b.id = sb.badge_id
            WHERE sb.student_id = ?
            ORDER BY sb.awarded_at DESC
        ", [$studentId]);
    }

    public function award(int $studentId, int $badgeId): bool
    {
        // Avoid duplicates
        $exists = $this->queryOne("SELECT id FROM student_badges WHERE student_id = ? AND badge_id = ?", [$studentId, $badgeId]);
        if ($exists) return false;
        return $this->execute("INSERT INTO student_badges (student_id, badge_id) VALUES (?, ?)", [$studentId, $badgeId]);
    }

    public function revoke(int $studentId, int $badgeId): bool
    {
        return $this->execute("DELETE FROM student_badges WHERE student_id = ? AND badge_id = ?", [$studentId, $badgeId]);
    }

    /**
     * Auto-award badges based on student activity.
     */
    public function autoAward(int $studentId): array
    {
        $awarded = [];
        $all = $this->getAll();
        foreach ($all as $badge) {
            if ($this->meetsCriteria($studentId, $badge)) {
                if ($this->award($studentId, $badge['id'])) $awarded[] = $badge;
            }
        }
        return $awarded;
    }

    private function meetsCriteria(int $studentId, array $badge): bool
    {
        $criteria = $badge['criteria'] ?? '';
        // criteria is a JSON string like {"min_skills":5,"min_portfolio":1,...}
        $data = json_decode($criteria, true) ?: [];
        if (empty($data)) return false;

        if (isset($data['min_skills'])) {
            $c = $this->queryOne("SELECT COUNT(*) as c FROM student_skills WHERE student_id = ?", [$studentId]);
            if ((int)($c['c'] ?? 0) < (int)$data['min_skills']) return false;
        }
        if (isset($data['min_portfolio'])) {
            $c = $this->queryOne("SELECT COUNT(*) as c FROM portfolios WHERE student_id = ?", [$studentId]);
            if ((int)($c['c'] ?? 0) < (int)$data['min_portfolio']) return false;
        }
        if (isset($data['min_applications'])) {
            $c = $this->queryOne("SELECT COUNT(*) as c FROM applications WHERE user_id = (SELECT user_id FROM students WHERE id = ?)", [$studentId]);
            if ((int)($c['c'] ?? 0) < (int)$data['min_applications']) return false;
        }
        if (isset($data['min_certificates'])) {
            $c = $this->queryOne("SELECT COUNT(*) as c FROM certificates WHERE student_id = ?", [$studentId]);
            if ((int)($c['c'] ?? 0) < (int)$data['min_certificates']) return false;
        }
        if (isset($data['profile_complete'])) {
            $s = $this->queryOne("SELECT profile_completion FROM students WHERE id = ?", [$studentId]);
            if ((int)($s['profile_completion'] ?? 0) < (int)$data['profile_complete']) return false;
        }
        return true;
    }
}
