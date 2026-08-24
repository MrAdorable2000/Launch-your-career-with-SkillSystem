<?php

namespace App\Models;

use PDO;

class StudentModel extends BaseModel
{
    protected string $table = 'students';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id', 'university_id', 'student_id_number', 'department', 'year_of_study', 'gpa', 'bio', 'skills_summary', 'linkedin', 'github', 'website', 'profile_completion'];

    public function findByUserId(int $userId): ?array
    {
        try {
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.avatar, u.phone,
                           uni.uni_name, uni.uni_code
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN universities uni ON s.university_id = uni.id
                    WHERE s.user_id = :user_id LIMIT 1";
            return $this->queryOne($sql, ['user_id' => $userId]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getSkills(int $studentId): array
    {
        try {
            $sql = "SELECT sk.*, ss.proficiency_level
                    FROM student_skills ss
                    JOIN skills sk ON ss.skill_id = sk.id
                    WHERE ss.student_id = :student_id
                    ORDER BY sk.name";
            return $this->query($sql, ['student_id' => $studentId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getEducation(int $studentId): array
    {
        try {
            return $this->query("SELECT * FROM education WHERE student_id = ? ORDER BY start_date DESC", [$studentId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getExperience(int $studentId): array
    {
        try {
            return $this->query("SELECT * FROM experience WHERE student_id = ? ORDER BY start_date DESC", [$studentId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getPortfolios(int $studentId): array
    {
        try {
            return $this->query("SELECT * FROM portfolios WHERE student_id = ? ORDER BY created_at DESC", [$studentId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCertificates(int $studentId): array
    {
        try {
            return $this->query("SELECT * FROM certificates WHERE student_id = ? ORDER BY issued_date DESC", [$studentId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getApplicationStats(int $userId): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'reviewing' THEN 1 ELSE 0 END) as reviewing,
                        SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                        SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview,
                        SUM(CASE WHEN status = 'offered' THEN 1 ELSE 0 END) as offered,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                    FROM applications WHERE user_id = ?";
            $result = $this->queryOne($sql, [$userId]);
            return $result ?: [
                'total' => 0, 'pending' => 0, 'reviewing' => 0,
                'shortlisted' => 0, 'interview' => 0, 'offered' => 0, 'rejected' => 0,
            ];
        } catch (\Throwable $e) {
            return [
                'total' => 0, 'pending' => 0, 'reviewing' => 0,
                'shortlisted' => 0, 'interview' => 0, 'offered' => 0, 'rejected' => 0,
            ];
        }
    }

    public function getAllWithUser(string $search = '', int $page = 1, int $perPage = 15): array
    {
        try {
            $where = "1=1";
            $params = [];
            if ($search) {
                $where .= " AND (u.first_name LIKE :s OR u.last_name LIKE :s OR s.department LIKE :s OR uni.uni_name LIKE :s)";
            }
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.status, uni.uni_name
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN universities uni ON s.university_id = uni.id
                    WHERE {$where} ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $countSql = "SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN universities uni ON s.university_id = uni.id WHERE {$where}";
            $stmt2 = $this->db->prepare($countSql);
            foreach ($params as $k => $v) $stmt2->bindValue(":{$k}", $v);
            $stmt2->execute();
            $total = (int) $stmt2->fetchColumn();

            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    /**
     * Alias for getAllWithUser with university filter (used by UniversityController).
     * Signature: (page, perPage, search, universityId)
     */
    public function getAllWithUsers(int $page = 1, int $perPage = 15, string $search = '', ?int $universityId = null): array
    {
        try {
            $where = "1=1";
            $params = [];
            if ($search) {
                $where .= " AND (u.first_name LIKE :s OR u.last_name LIKE :s OR s.department LIKE :s OR uni.uni_name LIKE :s)";
                $params['s'] = "%{$search}%";
            }
            if ($universityId) {
                $where .= " AND s.university_id = :uni_id";
                $params['uni_id'] = $universityId;
            }
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.status, uni.uni_name
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN universities uni ON s.university_id = uni.id
                    WHERE {$where} ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $countSql = "SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN universities uni ON s.university_id = uni.id WHERE {$where}";
            $stmt2 = $this->db->prepare($countSql);
            foreach ($params as $k => $v) $stmt2->bindValue(":{$k}", $v);
            $stmt2->execute();
            $total = (int) $stmt2->fetchColumn();

            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }
}
