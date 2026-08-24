<?php

namespace App\Models;

use PDO;

class AdminModel extends BaseModel
{
    protected string $table = 'administrators';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id', 'department'];

    public function findByUserId(int $userId): ?array
    {
        try {
            return $this->where('user_id', $userId)[0] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getDashboardStats(): array
    {
        try {
            $stats = [];
            $queries = [
                'total_users'         => "SELECT COUNT(*) FROM users",
                'total_students'      => "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'student'",
                'total_employers'     => "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'employer'",
                'active_jobs'         => "SELECT COUNT(*) FROM jobs WHERE status = 'published'",
                'active_internships'  => "SELECT COUNT(*) FROM internships WHERE status = 'published'",
                'open_freelance'      => "SELECT COUNT(*) FROM freelance_projects WHERE status = 'open'",
                'total_applications'  => "SELECT COUNT(*) FROM applications",
                'verified_companies'  => "SELECT COUNT(*) FROM companies WHERE verified = 1",
                'total_universities'  => "SELECT COUNT(*) FROM universities",
                'pending_reports'     => "SELECT COUNT(*) FROM reports WHERE status = 'pending'",
                'flagged_users'       => "SELECT COUNT(*) FROM users WHERE status = 'suspended' OR status = 'banned'",
            ];
            foreach ($queries as $key => $sql) {
                try {
                    $stats[$key] = (int) $this->db->query($sql)->fetchColumn();
                } catch (\Throwable $e) {
                    $stats[$key] = 0;
                }
            }
            // Revenue (float)
            try {
                $stats['total_revenue'] = (float) $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'")->fetchColumn();
            } catch (\Throwable $e) {
                $stats['total_revenue'] = 0;
            }
            return $stats;
        } catch (\Throwable $e) {
            return [
                'total_users' => 0, 'total_students' => 0, 'total_employers' => 0,
                'active_jobs' => 0, 'active_internships' => 0, 'open_freelance' => 0,
                'total_applications' => 0, 'verified_companies' => 0, 'total_universities' => 0,
                'pending_reports' => 0, 'total_revenue' => 0, 'flagged_users' => 0,
            ];
        }
    }

    public function getUserGrowthData(): array
    {
        try {
            return $this->query("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM users
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getApplicationTrendData(): array
    {
        try {
            return $this->query("
                SELECT DATE(applied_at) as date, COUNT(*) as count
                FROM applications
                WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE(applied_at)
                ORDER BY date
            ");
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getRecentAuditLogs(int $limit = 10): array
    {
        try {
            $sql = "SELECT al.*, u.first_name, u.last_name
                    FROM audit_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    ORDER BY al.created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getRoleDistribution(): array
    {
        try {
            return $this->query("
                SELECT r.name as role_name, r.slug as role_slug, COUNT(u.id) as count
                FROM roles r
                LEFT JOIN users u ON r.id = u.role_id
                GROUP BY r.id, r.name, r.slug
                ORDER BY count DESC
            ");
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function logAudit(int $userId, string $action, string $model, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            $sql = "INSERT INTO audit_logs (user_id, action, model, model_id, ip_address, user_agent, old_values, new_values)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sql)->execute([
                $userId, $action, $model, $modelId,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null
            ]);
        } catch (\Throwable $e) {
            return;
        }
    }

    // ================================================================
    // NEW METHODS (additive — do not modify existing methods above)
    // ================================================================

    /**
     * Jobs posted per month for the last 12 months.
     */
    public function getJobsGrowthData(): array
    {
        try {
            return $this->query("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                FROM jobs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month ORDER BY month
            ");
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Revenue per month for the last 12 months.
     */
    public function getRevenueData(): array
    {
        try {
            return $this->query("
                SELECT DATE_FORMAT(paid_at, '%Y-%m') as month, COALESCE(SUM(amount), 0) as total
                FROM payments
                WHERE status = 'completed' AND paid_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month ORDER BY month
            ");
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Recent registered users (for the latest-users table on the dashboard).
     */
    public function getRecentUsers(int $limit = 8): array
    {
        try {
            return $this->query("
                SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.avatar,
                       u.status, u.last_login_at, u.created_at,
                       r.name as role_name, r.slug as role_slug,
                       u.email_verified_at
                FROM users u
                JOIN roles r ON u.role_id = r.id
                ORDER BY u.created_at DESC LIMIT ?
            ", [$limit]);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Recent activity logs (for the activity timeline).
     */
    public function getRecentActivities(int $limit = 10): array
    {
        try {
            return $this->query("
                SELECT al.*, u.first_name, u.last_name, u.avatar
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC LIMIT ?
            ", [$limit]);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Count of unread notifications across all users.
     */
    public function getUnreadNotificationsCount(): int
    {
        try {
            return (int) $this->db->query("SELECT COUNT(*) FROM notifications WHERE read_at IS NULL")->fetchColumn();
        } catch (\Throwable $e) { return 0; }
    }

    /**
     * Count of unread messages across all users.
     */
    public function getUnreadMessagesCount(): int
    {
        try {
            return (int) $this->db->query("SELECT COUNT(*) FROM messages WHERE read_at IS NULL")->fetchColumn();
        } catch (\Throwable $e) { return 0; }
    }

    /**
     * Security center statistics.
     */
    public function getSecurityStats(): array
    {
        $defaults = [
            'failed_logins' => 0,
            'blocked_users' => 0,
            'suspended_users' => 0,
            'active_sessions' => 0,
            'recent_logins' => 0,
            'two_fa_enabled' => 0,
        ];
        try {
            // Failed logins: users with last_login_at NULL but created_at old (rough proxy)
            $defaults['blocked_users'] = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'banned'")->fetchColumn();
            $defaults['suspended_users'] = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'suspended'")->fetchColumn();
            // Active sessions: users who logged in in last 24h
            $defaults['active_sessions'] = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
            // Recent logins (last 7 days)
            $defaults['recent_logins'] = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
            // Login audit entries (failed login attempts tracked in audit_logs)
            $defaults['failed_logins'] = (int) $this->db->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'login' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        } catch (\Throwable $e) {}
        return $defaults;
    }

    /**
     * Top locations (used as proxy for "top countries").
     */
    public function getTopLocations(int $limit = 5): array
    {
        try {
            return $this->query("
                SELECT location, COUNT(*) as user_count
                FROM (
                    SELECT location FROM employers WHERE location IS NOT NULL AND location != ''
                    UNION ALL
                    SELECT location FROM universities WHERE location IS NOT NULL AND location != ''
                    UNION ALL
                    SELECT location FROM jobs WHERE location IS NOT NULL AND location != ''
                ) combined
                GROUP BY location
                ORDER BY user_count DESC LIMIT ?
            ", [$limit]);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Most active universities (by student count).
     */
    public function getTopUniversities(int $limit = 5): array
    {
        try {
            return $this->query("
                SELECT uni.uni_name, uni.location, COUNT(s.id) as student_count
                FROM universities uni
                LEFT JOIN students s ON s.university_id = uni.id
                GROUP BY uni.id, uni.uni_name, uni.location
                ORDER BY student_count DESC LIMIT ?
            ", [$limit]);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Most active companies (by job count).
     */
    public function getTopCompanies(int $limit = 5): array
    {
        try {
            return $this->query("
                SELECT e.company_name, e.industry, COUNT(j.id) as job_count
                FROM employers e
                LEFT JOIN jobs j ON j.employer_id = e.id
                GROUP BY e.id, e.company_name, e.industry
                ORDER BY job_count DESC LIMIT ?
            ", [$limit]);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Top skills by demand (from jobs requirements text).
     */
    public function getTopSkills(int $limit = 8): array
    {
        try {
            // Count how many jobs mention each skill
            $skills = $this->query("SELECT name FROM skills ORDER BY name");
            $result = [];
            foreach ($skills as $skill) {
                $name = $skill['name'];
                $count = (int) $this->db->query("SELECT COUNT(*) FROM jobs WHERE requirements LIKE '%" . $name . "%' OR description LIKE '%" . $name . "%'")->fetchColumn();
                if ($count > 0) $result[] = ['name' => $name, 'count' => $count];
            }
            usort($result, fn($a, $b) => $b['count'] <=> $a['count']);
            return array_slice($result, 0, $limit);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Students by department (faculty).
     */
    public function getStudentsByDepartment(): array
    {
        try {
            return $this->query("
                SELECT department, COUNT(*) as count
                FROM students
                WHERE department IS NOT NULL AND department != ''
                GROUP BY department
                ORDER BY count DESC LIMIT 10
            ");
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Revenue trend by month (for the revenue chart).
     */
    public function getMonthlyRevenue(): array
    {
        return $this->getRevenueData();
    }

    /**
     * Employment rate: % of applications that resulted in offers.
     */
    public function getEmploymentRate(): float
    {
        try {
            $total = (int) $this->db->query("SELECT COUNT(*) FROM applications")->fetchColumn();
            if ($total === 0) return 0.0;
            $offered = (int) $this->db->query("SELECT COUNT(*) FROM applications WHERE status IN ('offered', 'accepted')")->fetchColumn();
            return round(($offered / $total) * 100, 1);
        } catch (\Throwable $e) { return 0.0; }
    }

    /**
     * Count users by role slug.
     */
    public function countByRole(string $slug): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = ?");
            $stmt->execute([$slug]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) { return 0; }
    }

    /**
     * Get monthly growth percentage for a table.
     */
    public function getMonthlyGrowth(string $table): float
    {
        try {
            $thisMonth = (int) $this->db->query("SELECT COUNT(*) FROM {$table} WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
            $lastMonth = (int) $this->db->query("SELECT COUNT(*) FROM {$table} WHERE created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
            if ($lastMonth === 0) return $thisMonth > 0 ? 100.0 : 0.0;
            return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
        } catch (\Throwable $e) { return 0.0; }
    }
}
