<?php

namespace App\Models;

use PDO;

class EmployerModel extends BaseModel
{
    protected string $table = 'employers';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id', 'company_name', 'company_logo', 'industry', 'company_size', 'website', 'description', 'location', 'founded_year'];

    public function findByUserId(int $userId): ?array
    {
        try {
            $sql = "SELECT e.*, u.first_name, u.last_name, u.email, u.avatar
                    FROM employers e JOIN users u ON e.user_id = u.id
                    WHERE e.user_id = :user_id LIMIT 1";
            return $this->queryOne($sql, ['user_id' => $userId]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getAllWithUser(int $page = 1, int $perPage = 15): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT e.*, u.first_name, u.last_name, u.email, u.status
                    FROM employers e JOIN users u ON e.user_id = u.id
                    ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();
            $total = (int) $this->db->query("SELECT COUNT(*) FROM employers")->fetchColumn();
            return ['data' => $data, 'current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) ceil($total / $perPage)];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }
}
