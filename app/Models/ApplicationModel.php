<?php

namespace App\Models;

use PDO;

class ApplicationModel extends BaseModel
{
    protected string $table = 'applications';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id', 'job_id', 'internship_id', 'freelance_id', 'type', 'cover_letter', 'status'];

    public function getByUser(int $userId, int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT a.*,
                           COALESCE(j.title, i.title, f.title) as position_title,
                           COALESCE(c.name, 'N/A') as company_name
                    FROM applications a
                    LEFT JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN internships i ON a.internship_id = i.id
                    LEFT JOIN freelance_projects f ON a.freelance_id = f.id
                    LEFT JOIN companies c ON (j.company_id = c.id OR i.company_id = c.id)
                    WHERE a.user_id = :user_id
                    ORDER BY a.applied_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $cntStmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
            $cntStmt->execute([$userId]);
            $total = (int) $cntStmt->fetchColumn();

            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function getApplicantsByJob(int $jobId): array
    {
        try {
            $sql = "SELECT a.*, u.first_name, u.last_name, u.email, u.avatar,
                           s.department, s.university_id, uni.uni_name
                    FROM applications a
                    JOIN users u ON a.user_id = u.id
                    LEFT JOIN students s ON s.user_id = u.id
                    LEFT JOIN universities uni ON s.university_id = uni.id
                    WHERE a.job_id = :job_id
                    ORDER BY a.applied_at DESC";
            return $this->query($sql, ['job_id' => $jobId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function hasApplied(int $userId, int $jobId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND job_id = ?");
            $stmt->execute([$userId, $jobId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            return $this->db->prepare("UPDATE applications SET status = ? WHERE id = ?")->execute([$status, $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function countByStatus(string $status = ''): int
    {
        try {
            if ($status) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE status = ?");
                $stmt->execute([$status]);
                return (int) $stmt->fetchColumn();
            }
            return $this->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getStats(): array
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
                    FROM applications";
            $result = $this->queryOne($sql);
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

    /**
     * Get application stats filtered by a specific employer's jobs.
     * Fixes the bug where EmployerController::dashboard() used getStats()
     * (which returns GLOBAL stats) instead of employer-specific stats.
     */
    public function getStatsForEmployer(int $employerId): array
    {
        $empty = [
            'total' => 0, 'pending' => 0, 'reviewing' => 0,
            'shortlisted' => 0, 'interview' => 0, 'offered' => 0, 'rejected' => 0,
        ];
        if ($employerId <= 0) {
            return $empty;
        }
        try {
            $sql = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'reviewing' THEN 1 ELSE 0 END) as reviewing,
                        SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                        SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview,
                        SUM(CASE WHEN status = 'offered' THEN 1 ELSE 0 END) as offered,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                    FROM applications
                    WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)";
            $result = $this->queryOne($sql, [$employerId]);
            return $result ?: $empty;
        } catch (\Throwable $e) {
            return $empty;
        }
    }
}
