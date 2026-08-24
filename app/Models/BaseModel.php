<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class BaseModel
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /**
     * Set the table name dynamically (used by AdminController for settings).
     */
    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the table name directly and return self for chaining.
     */
    public function setTableDirect(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function find(int $id): ?array
    {
        if (!$this->db) return null;
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function all(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        if (!$this->db) return [];
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }

    public function whereMultiple(array $conditions, string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $where = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $param = str_replace('.', '_', $col);
            $where[] = "{$col} = :{$param}";
            $params[$param] = $val;
        }
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " ORDER BY {$orderBy} {$direction}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        $columns = implode(', ', array_keys($filtered));
        $placeholders = ':' . implode(', :', array_keys($filtered));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($filtered);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filtered)) return false;
        $set = [];
        foreach (array_keys($filtered) as $col) {
            $set[] = "{$col} = :{$col}";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
        $filtered['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($filtered);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count rows in the table.
     *
     * @param string $where  WHERE clause (without the "WHERE" keyword).
     *                       Use '?' placeholders for parameterized values.
     * @param array  $params Bind values for the '?' placeholders (prepared statement).
     * @return int
     */
    public function count(string $where = '', array $params = []): int
    {
        if (!$this->db) return 0;
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            if ($where) $sql .= " WHERE {$where}";
            // Use prepared statement if params are provided (H-5 security fix);
            // otherwise fall back to query() for backward compatibility with
            // callers that pass literal strings with no placeholders.
            if (!empty($params)) {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            } else {
                $stmt = $this->db->query($sql);
            }
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function paginate(int $page = 1, int $perPage = 10, string $where = '', string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$orderBy} {$direction} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $countSql .= " WHERE {$where}";
        $total = (int) $this->db->query($countSql)->fetchColumn();

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    public function query(string $sql, array $params = []): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function queryOne(string $sql, array $params = []): ?array
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function execute(string $sql, array $params = []): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function lastId(): string
    {
        return $this->db->lastInsertId();
    }
}