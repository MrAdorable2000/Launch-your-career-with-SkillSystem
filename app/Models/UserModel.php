<?php

namespace App\Models;

use PDO;

class UserModel extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    protected array $fillable = ['role_id', 'email', 'password', 'first_name', 'last_name', 'phone', 'avatar', 'status'];

    public function findByEmail(string $email): ?array
    {
        try {
            return $this->where('email', $email)[0] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function findWithRole(int $id): ?array
    {
        try {
            $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.id = :id LIMIT 1";
            return $this->queryOne($sql, ['id' => $id]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function findWithRoleByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.email = :email LIMIT 1";
            return $this->queryOne($sql, ['email' => $email]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getAllWithRoles(string $search = '', int $page = 1, int $perPage = 15): array
    {
        try {
            $where = "1=1";
            $params = [];
            if ($search) {
                $where .= " AND (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
                $params['search'] = "%{$search}%";
            }
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                    FROM users u JOIN roles r ON u.role_id = r.id
                    WHERE {$where} ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $countSql = "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE {$where}";
            $stmt2 = $this->db->prepare($countSql);
            foreach ($params as $k => $v) $stmt2->bindValue(":{$k}", $v);
            $stmt2->execute();
            $total = (int) $stmt2->fetchColumn();

            return [
                'data' => $data, 'current_page' => $page, 'per_page' => $perPage,
                'total' => $total, 'last_page' => (int) ceil($total / $perPage)
            ];
        } catch (\Throwable $e) {
            return ['data' => [], 'current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1];
        }
    }

    public function countByRole(string $roleSlug): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = :slug";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['slug' => $roleSlug]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function countByRoleSafe(string $roleSlug): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = ?");
            $stmt->execute([$roleSlug]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function updateLastLogin(int $id): void
    {
        try {
            $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$id]);
        } catch (\Throwable $e) {
            return;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            return $this->db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
