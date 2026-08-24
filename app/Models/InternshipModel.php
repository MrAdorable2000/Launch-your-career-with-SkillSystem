<?php

namespace App\Models;

use PDO;

class InternshipModel extends BaseModel
{
    protected string $table = 'internships';
    protected string $primaryKey = 'id';
    protected array $fillable = ['employer_id', 'company_id', 'title', 'description', 'requirements', 'duration', 'duration_unit', 'allowance', 'allowance_currency', 'location', 'deadline', 'positions_available', 'status'];

    public function getPublished(int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT i.*, c.name as company_name, c.logo as company_logo
                    FROM internships i
                    LEFT JOIN companies c ON i.company_id = c.id
                    WHERE i.status = 'published' AND i.deadline >= CURDATE()
                    ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $total = (int) $this->db->query("SELECT COUNT(*) FROM internships WHERE status = 'published' AND deadline >= CURDATE()")->fetchColumn();
            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function getByEmployer(int $employerId): array
    {
        try {
            $sql = "SELECT i.*, c.name as company_name,
                           (SELECT COUNT(*) FROM applications a WHERE a.internship_id = i.id) as applicant_count
                    FROM internships i
                    LEFT JOIN companies c ON i.company_id = c.id
                    WHERE i.employer_id = :employer_id
                    ORDER BY i.created_at DESC";
            return $this->query($sql, ['employer_id' => $employerId]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAllWithCompany(int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT i.*, c.name as company_name, e.company_name as employer_name
                    FROM internships i
                    LEFT JOIN companies c ON i.company_id = c.id
                    JOIN employers e ON i.employer_id = e.id
                    ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();
            $total = (int) $this->db->query("SELECT COUNT(*) FROM internships")->fetchColumn();
            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function countPublished(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM internships WHERE status = 'published'");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
