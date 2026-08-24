<?php
/**
 * LeaderboardModel — Student ranking by activity & profile score.
 * Computed from existing tables (no separate leaderboard table needed for runtime).
 */

namespace App\Models;

class LeaderboardModel extends BaseModel
{
    /**
     * Get top students by computed score:
     *   profile_completion + skills*2 + portfolio*3 + applications + certificates*2 + experience*2
     */
    public function topStudents(int $limit = 20, ?int $universityId = null): array
    {
        $sql = "
            SELECT s.id, s.user_id, u.first_name, u.last_name, u.avatar,
                   s.department, s.profile_completion, s.gpa,
                   uni.uni_name as university,
                   (SELECT COUNT(*) FROM student_skills ss WHERE ss.student_id = s.id) as skill_count,
                   (SELECT COUNT(*) FROM portfolios p WHERE p.student_id = s.id) as portfolio_count,
                   (SELECT COUNT(*) FROM applications a WHERE a.user_id = s.user_id) as application_count,
                   (SELECT COUNT(*) FROM certificates c WHERE c.student_id = s.id AND c.verified = 1) as cert_count,
                   (SELECT COUNT(*) FROM experience e WHERE e.student_id = s.id) as exp_count,
                   (
                     COALESCE(s.profile_completion, 0) +
                     (SELECT COUNT(*) FROM student_skills ss WHERE ss.student_id = s.id) * 2 +
                     (SELECT COUNT(*) FROM portfolios p WHERE p.student_id = s.id) * 3 +
                     (SELECT COUNT(*) FROM applications a WHERE a.user_id = s.user_id) * 1 +
                     (SELECT COUNT(*) FROM certificates c WHERE c.student_id = s.id AND c.verified = 1) * 4 +
                     (SELECT COUNT(*) FROM experience e WHERE e.student_id = s.id) * 2
                   ) as score
            FROM students s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN universities uni ON uni.id = s.university_id
            WHERE u.status = 'active'
        ";
        $params = [];
        if ($universityId) {
            $sql .= " AND s.university_id = ?";
            $params[] = $universityId;
        }
        $sql .= " ORDER BY score DESC, u.first_name ASC LIMIT " . (int)$limit;

        return $this->query($sql, $params);
    }

    public function getStudentRank(int $studentId): array
    {
        $top = $this->topStudents(1000);
        foreach ($top as $i => $s) {
            if ((int)$s['id'] === $studentId) {
                return ['rank' => $i + 1, 'score' => $s['score'], 'total' => count($top)];
            }
        }
        return ['rank' => 0, 'score' => 0, 'total' => count($top)];
    }
}
