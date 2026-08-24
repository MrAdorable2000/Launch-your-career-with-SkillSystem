<?php

namespace App\Models;

use PDO;

class JobModel extends BaseModel
{
    protected string $table = 'jobs';
    protected string $primaryKey = 'id';
    protected array $fillable = ['employer_id', 'company_id', 'title', 'description', 'requirements', 'responsibilities', 'salary_min', 'salary_max', 'salary_currency', 'location', 'type', 'remote', 'deadline', 'status'];

    public function getPublished(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $where = "j.status = 'published' AND j.deadline >= CURDATE()";
            $params = [];

            if (!empty($filters['type'])) {
                $where .= " AND j.type = :type";
                $params['type'] = $filters['type'];
            }
            if (!empty($filters['location'])) {
                $where .= " AND j.location LIKE :location";
                $params['location'] = "%{$filters['location']}%";
            }
            if (!empty($filters['remote'])) {
                $where .= " AND j.remote = 1";
            }
            if (!empty($filters['search'])) {
                $where .= " AND (j.title LIKE :search OR j.description LIKE :search OR c.name LIKE :search)";
                $params['search'] = "%{$filters['search']}%";
            }

            $offset = ($page - 1) * $perPage;
            $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, e.company_name as employer_name
                    FROM jobs j
                    LEFT JOIN companies c ON j.company_id = c.id
                    JOIN employers e ON j.employer_id = e.id
                    WHERE {$where}
                    ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $countSql = "SELECT COUNT(*) FROM jobs j LEFT JOIN companies c ON j.company_id = c.id WHERE {$where}";
            $stmt2 = $this->db->prepare($countSql);
            foreach ($params as $k => $v) $stmt2->bindValue(":{$k}", $v);
            $stmt2->execute();
            $total = (int) $stmt2->fetchColumn();

            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function getByEmployer(int $employerId, int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo,
                           (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as applicant_count
                    FROM jobs j
                    LEFT JOIN companies c ON j.company_id = c.id
                    WHERE j.employer_id = :employer_id
                    ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':employer_id', $employerId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $cntStmt = $this->db->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
            $cntStmt->execute([$employerId]);
            $total = (int) $cntStmt->fetchColumn();

            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function getWithCompany(int $id): ?array
    {
        try {
            $sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, c.location as company_location,
                           e.company_name as employer_name
                    FROM jobs j
                    LEFT JOIN companies c ON j.company_id = c.id
                    JOIN employers e ON j.employer_id = e.id
                    WHERE j.id = :id LIMIT 1";
            return $this->queryOne($sql, ['id' => $id]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function incrementViews(int $id): void
    {
        try {
            $this->db->prepare("UPDATE jobs SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);
        } catch (\Throwable $e) {
            return;
        }
    }

    public function countByStatus(string $status = ''): int
    {
        try {
            if ($status) {
                return (int) $this->count("status = " . $this->db->quote($status));
            }
            return $this->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function countPublished(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'published'");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getAllWithCompany(int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT j.*, c.name as company_name, e.company_name as employer_name, e.user_id
                    FROM jobs j
                    LEFT JOIN companies c ON j.company_id = c.id
                    JOIN employers e ON j.employer_id = e.id
                    ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $total = (int) $this->db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }
}
