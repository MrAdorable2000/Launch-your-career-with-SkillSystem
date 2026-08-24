<?php

namespace App\Models;

use PDO;

class FreelanceModel extends BaseModel
{
    protected string $table = 'freelance_projects';
    protected string $primaryKey = 'id';
    protected array $fillable = ['employer_id', 'title', 'description', 'budget_min', 'budget_max', 'currency', 'skills_required', 'deadline', 'status'];

    public function getOpen(int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT f.*, e.company_name,
                           (SELECT COUNT(*) FROM applications a WHERE a.freelance_id = f.id) as bid_count
                    FROM freelance_projects f
                    JOIN employers e ON f.employer_id = e.id
                    WHERE f.status = 'open'
                    ORDER BY f.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();
            $total = (int) $this->db->query("SELECT COUNT(*) FROM freelance_projects WHERE status = 'open'")->fetchColumn();
            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function getByEmployer(int $employerId): array
    {
        try {
            $sql = "SELECT f.*, (SELECT COUNT(*) FROM applications a WHERE a.freelance_id = f.id) as bid_count
                    FROM freelance_projects f
                    WHERE f.employer_id = :employer_id
                    ORDER BY f.created_at DESC";
            return $this->query($sql, ['employer_id' => $employerId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAllWithEmployer(int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT f.*, e.company_name
                    FROM freelance_projects f
                    JOIN employers e ON f.employer_id = e.id
                    ORDER BY f.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();
            $total = (int) $this->db->query("SELECT COUNT(*) FROM freelance_projects")->fetchColumn();
            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }
}
