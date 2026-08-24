<?php

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\CSRF;
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\EmployerModel;
use App\Models\JobModel;
use App\Models\InternshipModel;
use App\Models\FreelanceModel;
use App\Models\ApplicationModel;
use App\Models\BaseModel;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class AdminController extends BaseController
{
    private AdminModel $adminModel;
    private UserModel $userModel;
    private JobModel $jobModel;
    private InternshipModel $internModel;
    private FreelanceModel $freelanceModel;
    private ApplicationModel $appModel;

    public function __construct()
    {
        // CRITICAL: Enforce authentication + admin role on EVERY admin route
        AuthMiddleware::handle();
        RoleMiddleware::handle(['admin']);

        $this->adminModel = new AdminModel();
        $this->userModel = new UserModel();
        $this->jobModel = new JobModel();
        $this->internModel = new InternshipModel();
        $this->freelanceModel = new FreelanceModel();
        $this->appModel = new ApplicationModel();
    }

    public function dashboard(): void
    {
        $stats = $this->adminModel->getDashboardStats();
        $userGrowth = $this->adminModel->getUserGrowthData();
        $appTrend = $this->adminModel->getApplicationTrendData();
        $roleDist = $this->adminModel->getRoleDistribution();
        $recentLogs = $this->adminModel->getRecentAuditLogs(10);

        // Extract frequently-used stats values
        $pendingReports = (int)($stats['pending_reports'] ?? 0);
        $flaggedUsers   = (int)($stats['flagged_users'] ?? 0);

        // NEW: Richer data for the premium dashboard (additive)
        $jobsGrowth = $this->adminModel->getJobsGrowthData();
        $revenueData = $this->adminModel->getRevenueData();
        $recentUsers = $this->adminModel->getRecentUsers(8);
        $recentActivities = $this->adminModel->getRecentActivities(10);
        $securityStats = $this->adminModel->getSecurityStats();
        $topLocations = $this->adminModel->getTopLocations(5);
        $topUniversities = $this->adminModel->getTopUniversities(5);
        $topCompanies = $this->adminModel->getTopCompanies(5);
        $topSkills = $this->adminModel->getTopSkills(8);
        $deptData = $this->adminModel->getStudentsByDepartment();
        $employmentRate = $this->adminModel->getEmploymentRate();
        $unreadNotifs = $this->adminModel->getUnreadNotificationsCount();
        $unreadMsgs = $this->adminModel->getUnreadMessagesCount();
        $mentorCount = $this->adminModel->countByRole('mentor');
        $certCount = 0;
        try {
            $certCount = (int)(new BaseModel())->query("SELECT COUNT(*) as c FROM certificates")[0]['c'];
        } catch (\Throwable $e) {}

        $growth = [
            'users' => $this->adminModel->getMonthlyGrowth('users'),
            'jobs' => $this->adminModel->getMonthlyGrowth('jobs'),
            'applications' => $this->adminModel->getMonthlyGrowth('applications'),
            'payments' => $this->adminModel->getMonthlyGrowth('payments'),
        ];

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'mysql_version' => '8.0+',
            'app_version' => '3.0.0',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache',
            'max_upload' => ini_get('upload_max_filesize'),
            'memory_limit' => ini_get('memory_limit'),
            'disk_free' => function_exists('disk_free_space') ? @disk_free_space('.') : false,
            'disk_total' => function_exists('disk_total_space') ? @disk_total_space('.') : false,
        ];

        // NEW: Additional data for enhanced dashboard
        $paymentCount = 0;
        $messageCount = 0;
        $inactiveUsers = 0;
        $inactiveCompanies = 0;
        $pendingEmployerVerif = 0;
        $pendingUniVerif = 0;
        $passwordResets = 0;
        try {
            $base = new BaseModel();
            $paymentCount = (int)$base->query("SELECT COUNT(*) as c FROM payments")[0]['c'];
            $messageCount = (int)$base->query("SELECT COUNT(*) as c FROM messages")[0]['c'];
            $inactiveUsers = (int)$base->query("SELECT COUNT(*) as c FROM users WHERE status = 'inactive' OR last_login_at IS NULL")[0]['c'];
            $inactiveCompanies = (int)$base->query("SELECT COUNT(*) as c FROM employers WHERE company_name IS NOT NULL AND id NOT IN (SELECT DISTINCT employer_id FROM jobs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY))")[0]['c'];
            $pendingEmployerVerif = (int)$base->query("SELECT COUNT(*) as c FROM companies WHERE verified = 0")[0]['c'];
            $pendingUniVerif = (int)$base->query("SELECT COUNT(*) as c FROM universities WHERE total_students = 0 OR total_students IS NULL")[0]['c'];
            $passwordResets = (int)$base->query("SELECT COUNT(*) as c FROM password_resets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")[0]['c'];
        } catch (\Throwable $e) {}

        // AI Insights (rule-based)
        $aiInsights = [
            'predicted_growth' => round(max(0, $growth['users']) * 1.15, 0),
            'hiring_forecast' => round(max(0, $growth['jobs']) * 1.10, 0),
            'inactive_users' => $inactiveUsers,
            'inactive_companies' => $inactiveCompanies,
            'risk_level' => ($flaggedUsers > 5 || $pendingReports > 5) ? 'high' : ($flaggedUsers > 0 || $pendingReports > 0 ? 'medium' : 'low'),
            'recommended_actions' => [],
        ];
        if ($inactiveUsers > 10) $aiInsights['recommended_actions'][] = ['icon' => 'fa-user-clock', 'text' => 'Send re-engagement emails to ' . $inactiveUsers . ' inactive users'];
        if ($pendingEmployerVerif > 0) $aiInsights['recommended_actions'][] = ['icon' => 'fa-building', 'text' => 'Review ' . $pendingEmployerVerif . ' pending employer verifications'];
        if ($pendingReports > 0) $aiInsights['recommended_actions'][] = ['icon' => 'fa-flag', 'text' => 'Resolve ' . $pendingReports . ' pending user reports'];
        if ($growth['jobs'] < 0) $aiInsights['recommended_actions'][] = ['icon' => 'fa-chart-line', 'text' => 'Job postings declined ' . abs($growth['jobs']) . '% — consider outreach to employers'];
        if (empty($aiInsights['recommended_actions'])) $aiInsights['recommended_actions'][] = ['icon' => 'fa-check-circle', 'text' => 'All systems healthy — no action required'];

        // Command Center data
        $commandCenter = [
            'pending_reports' => $pendingReports,
            'pending_employer_verif' => $pendingEmployerVerif,
            'pending_uni_verif' => $pendingUniVerif,
            'unread_messages' => $unreadMsgs,
            'unread_notifications' => $unreadNotifs,
            'flagged_users' => $flaggedUsers,
            'password_resets' => $passwordResets,
            'critical_alerts' => ($flaggedUsers > 5 ? 1 : 0) + ($pendingReports > 5 ? 1 : 0),
        ];

        // FIX: Fetch recent notifications for the admin dashboard widget.
        // Previously this variable was missing, causing an "Undefined variable" notice
        // and the notifications card always showed the empty state.
        $recentNotifications = [];
        try {
            $notifModel = new \App\Models\NotificationModel();
            $recentNotifications = $notifModel->getRecentForUser(Session::userId(), 5);
        } catch (\Throwable $e) {}

        $this->view('admin/dashboard', [
            'stats' => $stats,
            'userGrowth' => $userGrowth,
            'appTrend' => $appTrend,
            'roleDist' => $roleDist,
            'recentLogs' => $recentLogs,
            'jobsGrowth' => $jobsGrowth,
            'revenueData' => $revenueData,
            'recentUsers' => $recentUsers,
            'recentActivities' => $recentActivities,
            'securityStats' => $securityStats,
            'topLocations' => $topLocations,
            'topUniversities' => $topUniversities,
            'topCompanies' => $topCompanies,
            'topSkills' => $topSkills,
            'deptData' => $deptData,
            'employmentRate' => $employmentRate,
            'unreadNotifs' => $unreadNotifs,
            'unreadMsgs' => $unreadMsgs,
            'mentorCount' => $mentorCount,
            'certCount' => $certCount,
            'growth' => $growth,
            'systemInfo' => $systemInfo,
            'paymentCount' => $paymentCount,
            'messageCount' => $messageCount,
            'inactiveUsers' => $inactiveUsers,
            'inactiveCompanies' => $inactiveCompanies,
            'aiInsights' => $aiInsights,
            'commandCenter' => $commandCenter,
            'passwordResets' => $passwordResets,
            'recentNotifications' => $recentNotifications,
        ]);
    }

    /**
     * Admin creates a new user account directly.
     */
    public function createUser(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('admin/users');
            return;
        }

        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        $email = $this->post('email');
        $phone = $this->post('phone');
        $roleSlug = $this->post('role');
        $password = $_POST['password'] ?? 'password';

        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email) || empty($roleSlug)) {
            Flash::error('First name, last name, email, and role are required.');
            $this->redirect('admin/users');
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please enter a valid email address.');
            $this->redirect('admin/users');
            return;
        }

        // Check email uniqueness
        $existing = $this->userModel->findByEmail($email);
        if ($existing) {
            Flash::error('A user with this email already exists.');
            $this->redirect('admin/users');
            return;
        }

        // Get role ID from slug
        $roleMap = ['admin' => 1, 'student' => 2, 'employer' => 3, 'university' => 4, 'mentor' => 5];
        $roleId = $roleMap[$roleSlug] ?? 2;

        // Hash password
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Create user
        $base = new BaseModel();
        $base->execute(
            "INSERT INTO users (role_id, email, password, first_name, last_name, phone, email_verified_at, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'active')",
            [$roleId, $email, $hashed, $firstName, $lastName, $phone ?: null]
        );
        $userId = (int)$base->lastId();

        // Create role-specific profile
        if ($roleSlug === 'student') {
            $base->execute("INSERT INTO students (user_id, profile_completion) VALUES (?, 10)", [$userId]);
        } elseif ($roleSlug === 'employer') {
            $base->execute("INSERT INTO employers (user_id, company_name) VALUES (?, ?)", [$userId, $firstName . "'s Company"]);
        } elseif ($roleSlug === 'university') {
            $base->execute("INSERT INTO universities (user_id, uni_name) VALUES (?, ?)", [$userId, $firstName . "'s University"]);
        } elseif ($roleSlug === 'mentor') {
            $base->execute("INSERT INTO mentors (user_id, specialization, title, availability, rating, total_sessions) VALUES (?, 'General', 'Mentor', 'available', 0, 0)", [$userId]);
        } elseif ($roleSlug === 'admin') {
            $base->execute("INSERT INTO administrators (user_id, department) VALUES (?, 'Administration')", [$userId]);
        }

        // Audit log
        $this->adminModel->logAudit(Session::userId(), 'create', 'users', $userId, null, ['email' => $email, 'role' => $roleSlug]);
        $this->logActivity('admin', 'Created new user: ' . $email . ' (' . $roleSlug . ')');

        Flash::success('User created successfully! Default password: ' . $password);
        $this->redirect('admin/users');
    }

    public function users(): void
    {
        $page = $this->getInt('page', 1);
        $search = $this->post('search') ?: ($_GET['search'] ?? '');
        $roleFilter = $_GET['role'] ?? '';
        $users = $this->userModel->getAllWithRoles($search, $page, 15);

        // If a role filter is set, filter the users array
        if (!empty($roleFilter)) {
            $filtered = ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1, 'per_page' => 15];
            foreach (($users['data'] ?? $users) as $u) {
                if (($u['role_slug'] ?? '') === $roleFilter) {
                    $filtered['data'][] = $u;
                    $filtered['total']++;
                }
            }
            $users = $filtered;
        }

        $roleNames = [
            'student' => 'Students', 'employer' => 'Employers',
            'university' => 'Universities', 'mentor' => 'Mentors', 'admin' => 'Admins'
        ];

        $this->view('admin/users', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'roleFilterName' => $roleNames[$roleFilter] ?? 'All Users'
        ]);
    }

    // ============================================================
    // NEW ADMIN PAGES (additive)
    // ============================================================

    public function applications(): void
    {
        $page = $this->getInt('page', 1);
        $base = new BaseModel();
        $apps = $base->query("
            SELECT a.*, u.first_name, u.last_name, u.email,
                   COALESCE(j.title, i.title, f.title) as position_title,
                   e.company_name
            FROM applications a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN jobs j ON a.job_id = j.id
            LEFT JOIN internships i ON a.internship_id = i.id
            LEFT JOIN freelance_projects f ON a.freelance_id = f.id
            LEFT JOIN employers e ON e.id = COALESCE(j.employer_id, i.employer_id)
            ORDER BY a.applied_at DESC LIMIT 100
        ");
        $stats = [
            'total' => count($apps),
            'pending' => 0, 'reviewing' => 0, 'shortlisted' => 0,
            'interview' => 0, 'offered' => 0, 'rejected' => 0,
        ];
        foreach ($apps as $a) {
            $st = $a['status'] ?? 'pending';
            if (isset($stats[$st])) $stats[$st]++;
        }
        $this->view('admin/applications', ['applications' => $apps, 'stats' => $stats]);
    }

    public function certificates(): void
    {
        $base = new BaseModel();
        $certs = $base->query("
            SELECT c.*, u.first_name, u.last_name, u.email, s.department
            FROM certificates c
            LEFT JOIN students s ON s.id = c.student_id
            LEFT JOIN users u ON u.id = s.user_id
            ORDER BY c.created_at DESC LIMIT 100
        ");
        $stats = [
            'total' => count($certs),
            'verified' => 0,
            'pending' => 0,
        ];
        foreach ($certs as $c) {
            if (!empty($c['verified'])) $stats['verified']++;
            else $stats['pending']++;
        }
        $this->view('admin/certificates', ['certificates' => $certs, 'stats' => $stats]);
    }

    public function payments(): void
    {
        $base = new BaseModel();
        $payments = $base->query("
            SELECT p.*, u.first_name, u.last_name, u.email
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC LIMIT 100
        ");
        $totalRevenue = 0;
        $completed = 0;
        $pending = 0;
        $failed = 0;
        $refunded = 0;
        foreach ($payments as $p) {
            if ($p['status'] === 'completed') {
                $totalRevenue += (float)$p['amount'];
                $completed++;
            } elseif ($p['status'] === 'pending') $pending++;
            elseif ($p['status'] === 'failed') $failed++;
            elseif ($p['status'] === 'refunded') $refunded++;
        }

        // Load payment settings
        $settingsModel = new BaseModel();
        $settingsModel->setTable('settings');
        $allSettings = $settingsModel->all('key', 'ASC');
        $settings = [];
        foreach ($allSettings as $s) {
            $settings[$s['key']] = $s['value'];
        }

        // Payment methods status
        $paymentMethods = [
            'stripe' => [
                'name' => 'Stripe',
                'icon' => 'fab fa-stripe',
                'color' => 'primary',
                'enabled' => in_array(($settings['payment_provider'] ?? ''), ['stripe', 'both']),
                'config' => [
                    'Public Key' => $settings['stripe_public_key'] ?? '',
                    'Secret Key' => !empty($settings['stripe_secret_key'] ?? '') ? '••••••••••••' : 'Not configured',
                ],
            ],
            'paypal' => [
                'name' => 'PayPal',
                'icon' => 'fab fa-paypal',
                'color' => 'info',
                'enabled' => in_array(($settings['payment_provider'] ?? ''), ['paypal', 'both']),
                'config' => [
                    'Client ID' => $settings['paypal_client_id'] ?? '',
                    'Client Secret' => !empty($settings['paypal_client_secret'] ?? '') ? '••••••••••••' : 'Not configured',
                ],
            ],
            'mtn_momo' => [
                'name' => 'MTN MoMo',
                'icon' => 'fas fa-mobile-alt',
                'color' => 'warning',
                'enabled' => ($settings['mtn_momo_enabled'] ?? '0') === '1',
                'config' => [
                    'API User ID' => $settings['mtn_momo_api_user'] ?? 'Not configured',
                    'Subscription Key' => !empty($settings['mtn_momo_subscription_key'] ?? '') ? '••••••••••••' : 'Not configured',
                ],
            ],
            'airtel_money' => [
                'name' => 'Airtel Money',
                'icon' => 'fas fa-wallet',
                'color' => 'danger',
                'enabled' => ($settings['airtel_money_enabled'] ?? '0') === '1',
                'config' => [
                    'API Key' => !empty($settings['airtel_money_api_key'] ?? '') ? '••••••••••••' : 'Not configured',
                    'Secret' => !empty($settings['airtel_money_secret'] ?? '') ? '••••••••••••' : 'Not configured',
                ],
            ],
            'bank_transfer' => [
                'name' => 'Bank Transfer',
                'icon' => 'fas fa-university',
                'color' => 'success',
                'enabled' => ($settings['bank_transfer_enabled'] ?? '1') === '1',
                'config' => [
                    'Bank Name' => $settings['bank_name'] ?? 'Bank of Kigali',
                    'Account Number' => $settings['bank_account_number'] ?? '0000000000000',
                ],
            ],
        ];

        // Plan pricing
        $plans = [
            'free' => ['name' => 'Free Plan', 'price' => 0, 'max_apps' => (int)($settings['free_plan_max_applications'] ?? 10), 'color' => 'soft'],
            'basic' => ['name' => 'Basic Plan', 'price' => (int)($settings['basic_plan_price'] ?? 20000), 'color' => 'primary'],
            'premium' => ['name' => 'Premium Plan', 'price' => (int)($settings['premium_plan_price'] ?? 50000), 'color' => 'success'],
            'enterprise' => ['name' => 'Enterprise Plan', 'price' => (int)($settings['enterprise_plan_price'] ?? 150000), 'color' => 'warning'],
        ];

        $this->view('admin/payments', [
            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
            'completed' => $completed,
            'pending' => $pending,
            'failed' => $failed,
            'refunded' => $refunded,
            'total' => count($payments),
            'settings' => $settings,
            'paymentMethods' => $paymentMethods,
            'plans' => $plans,
            'csrfField' => CSRF::field(),
        ]);
    }

    public function analytics(): void
    {
        $stats = $this->adminModel->getDashboardStats();
        $userGrowth = $this->adminModel->getUserGrowthData();
        $appTrend = $this->adminModel->getApplicationTrendData();
        $jobsGrowth = $this->adminModel->getJobsGrowthData();
        $revenueData = $this->adminModel->getRevenueData();
        $roleDist = $this->adminModel->getRoleDistribution();
        $topSkills = $this->adminModel->getTopSkills(10);
        $deptData = $this->adminModel->getStudentsByDepartment();
        $topUniversities = $this->adminModel->getTopUniversities(10);
        $topCompanies = $this->adminModel->getTopCompanies(10);
        $employmentRate = $this->adminModel->getEmploymentRate();
        $this->view('admin/analytics', [
            'stats' => $stats, 'userGrowth' => $userGrowth, 'appTrend' => $appTrend,
            'jobsGrowth' => $jobsGrowth, 'revenueData' => $revenueData,
            'roleDist' => $roleDist, 'topSkills' => $topSkills,
            'deptData' => $deptData, 'topUniversities' => $topUniversities,
            'topCompanies' => $topCompanies, 'employmentRate' => $employmentRate,
        ]);
    }

    public function security(): void
    {
        $securityStats = $this->adminModel->getSecurityStats();
        $recentLogs = $this->adminModel->getRecentAuditLogs(20);
        $flaggedUsers = (new BaseModel())->query("
            SELECT id, first_name, last_name, email, status, created_at
            FROM users WHERE status IN ('suspended', 'banned')
            ORDER BY updated_at DESC LIMIT 20
        ");
        $this->view('admin/security', [
            'securityStats' => $securityStats,
            'recentLogs' => $recentLogs,
            'flaggedUsers' => $flaggedUsers,
        ]);
    }

    public function systemHealth(): void
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'mysql_version' => '8.0+',
            'app_version' => '3.0.0',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache',
            'max_upload' => ini_get('upload_max_filesize'),
            'memory_limit' => ini_get('memory_limit'),
            'disk_free' => function_exists('disk_free_space') ? @disk_free_space('.') : false,
            'disk_total' => function_exists('disk_total_space') ? @disk_total_space('.') : false,
            'php_modules' => get_loaded_extensions(),
        ];
        $pendingReports = (int)($this->adminModel->getDashboardStats()['pending_reports'] ?? 0);
        $this->view('admin/system-health', [
            'systemInfo' => $systemInfo,
            'pendingReports' => $pendingReports,
        ]);
    }

    public function backup(): void
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'disk_free' => function_exists('disk_free_space') ? @disk_free_space('.') : false,
            'disk_total' => function_exists('disk_total_space') ? @disk_total_space('.') : false,
        ];
        $this->view('admin/backup', ['systemInfo' => $systemInfo]);
    }

    public function reports(): void
    {
        $stats = $this->adminModel->getDashboardStats();
        $roleDist = $this->adminModel->getRoleDistribution();
        $topUniversities = $this->adminModel->getTopUniversities(10);
        $topCompanies = $this->adminModel->getTopCompanies(10);
        $deptData = $this->adminModel->getStudentsByDepartment();
        $employmentRate = $this->adminModel->getEmploymentRate();
        $this->view('admin/reports', [
            'stats' => $stats, 'roleDist' => $roleDist,
            'topUniversities' => $topUniversities, 'topCompanies' => $topCompanies,
            'deptData' => $deptData, 'employmentRate' => $employmentRate,
        ]);
    }

    /**
     * Full profile edit (name, email, phone, role) — separate from updateUserStatus,
     * which only toggles active/inactive/suspended/banned.
     */
    public function updateUser(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        $email = $this->post('email');
        $phone = $this->post('phone');
        $roleSlug = $this->post('role');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $this->json(['success' => false, 'message' => 'First name, last name, and email are required.'], 400);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 400);
            return;
        }

        // Ensure the email isn't taken by a different user
        $existing = $this->userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== $id) {
            $this->json(['success' => false, 'message' => 'That email is already used by another account.'], 400);
            return;
        }

        // Admin accounts cannot have their role changed (safety guard)
        $roleMap = ['admin' => 1, 'student' => 2, 'employer' => 3, 'university' => 4, 'mentor' => 5];
        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone ?: null,
        ];
        if ($roleSlug && (int)$user['role_id'] !== 1 && isset($roleMap[$roleSlug])) {
            $updateData['role_id'] = $roleMap[$roleSlug];
        }

        $this->userModel->update($id, $updateData);
        $this->adminModel->logAudit(Session::userId(), 'update', 'users', $id,
            ['first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'email' => $user['email']],
            $updateData
        );
        $this->logActivity('admin', 'Updated user profile: ' . $email);

        $this->json(['success' => true, 'message' => 'User updated successfully.']);
    }

    public function updateUserStatus(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $status = $this->post('status');
        $allowed = ['active', 'inactive', 'suspended', 'banned'];
        if (!in_array($status, $allowed)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        // Protect admin accounts from being banned or suspended
        if ((int)$user['role_id'] === 1 && in_array($status, ['suspended', 'banned', 'inactive'])) {
            $this->json(['success' => false, 'message' => 'Admin accounts cannot be suspended or banned'], 403);
            return;
        }

        $this->userModel->updateStatus($id, $status);
        $this->adminModel->logAudit(Session::userId(), 'update', 'users', $id, ['status' => $user['status']], ['status' => $status]);

        $this->json(['success' => true, 'message' => 'User status updated to ' . $status]);
    }

    /**
     * Permanently delete a user and all their related data.
     * Cascade deletes: students, employers, mentors, administrators, applications,
     * messages, notifications, discussions, comments, ratings, subscriptions, payments,
     * reports, activity_logs (SET NULL), audit_logs (SET NULL).
     */
    public function deleteUser(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        // Prevent admin from deleting themselves
        if ($id === Session::userId()) {
            $this->json(['success' => false, 'message' => 'You cannot delete your own account'], 400);
            return;
        }

        // Prevent deleting other admins (optional safety)
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        if ($user['role_id'] == 1) {
            // Admin accounts cannot be deleted at all
            $this->json(['success' => false, 'message' => 'Admin accounts cannot be deleted'], 403);
            return;
        }

        // Log the audit BEFORE deleting (user_id will become NULL after delete)
        $this->adminModel->logAudit(
            Session::userId(),
            'delete',
            'users',
            $id,
            ['email' => $user['email'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name']],
            null
        );

        // Delete the user — cascading FKs will clean up all related tables
        $deleted = $this->userModel->delete($id);

        if ($deleted) {
            $this->logActivity('delete', 'Deleted user: ' . $user['email']);
            $this->json(['success' => true, 'message' => 'User permanently deleted']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }

    public function manageJobs(): void
    {
        $page = $this->getInt('page', 1);
        $jobs = $this->jobModel->getAllWithCompany($page, 15);

        $this->view('admin/manage-jobs', ['jobs' => $jobs]);
    }

    public function updateJobStatus(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        // H-6 fix: Whitelist allowed job statuses to prevent arbitrary values
        $status = $this->post('status');
        $allowedStatuses = ['draft', 'published', 'closed', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            $this->json(['success' => false, 'message' => 'Invalid status value'], 400);
            return;
        }

        $this->jobModel->update($id, ['status' => $status]);
        $this->adminModel->logAudit(Session::userId(), 'update', 'jobs', $id, null, ['status' => $status]);

        $this->json(['success' => true, 'message' => 'Job status updated']);
    }

    public function manageInternships(): void
    {
        $page = $this->getInt('page', 1);
        $internships = $this->internModel->getAllWithCompany($page, 15);

        $this->view('admin/manage-internships', ['internships' => $internships]);
    }

    public function updateInternshipStatus(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $status = $this->post('status');
        $allowedStatuses = ['draft', 'published', 'closed', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            $this->json(['success' => false, 'message' => 'Invalid status value'], 400);
            return;
        }

        $this->internModel->update($id, ['status' => $status]);
        $this->adminModel->logAudit(Session::userId(), 'update', 'internships', $id, null, ['status' => $status]);

        $this->json(['success' => true, 'message' => 'Internship status updated']);
    }

    public function settings(): void
    {
        $settingsModel = new BaseModel();
        $settingsModel->setTable('settings');

        // Always ensure all default settings exist (INSERT IGNORE skips existing keys)
        $defaults = [
            ['site_name', 'SkillSystem', 'string'],
            ['site_description', 'Student Skills, Internship & Career Management System', 'string'],
            ['site_tagline', 'Connect. Learn. Succeed.', 'string'],
            ['site_url', APP_URL, 'string'],
            ['site_email', 'noreply@skillsystem.rw', 'string'],
            ['site_logo', '', 'string'],
            ['site_favicon', '', 'string'],
            ['site_keywords', 'skills, education, jobs, careers, internships, Rwanda', 'string'],
            ['support_email', 'support@skillsystem.rw', 'string'],
            ['contact_phone', '+250788000001', 'string'],
            ['contact_address', 'Kigali, Rwanda', 'string'],
            ['footer_text', 'SkillSystem — Connecting student talent with real-world opportunities.', 'string'],
            ['timezone', 'Africa/Kigali', 'string'],
            ['language', 'en', 'string'],
            ['default_currency', 'RWF', 'string'],
            ['max_file_upload_size', '5242880', 'integer'],
            ['allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx', 'string'],
            ['posts_per_page', '10', 'integer'],
            ['enable_registration', '1', 'boolean'],
            ['enable_dark_mode', '1', 'boolean'],
            ['require_email_verification', '0', 'boolean'],
            ['maintenance_mode', '0', 'boolean'],
            ['enable_jobs', '1', 'boolean'],
            ['enable_internships', '1', 'boolean'],
            ['enable_freelance', '1', 'boolean'],
            ['enable_mentorship', '1', 'boolean'],
            ['enable_forum', '1', 'boolean'],
            ['enable_certificates', '1', 'boolean'],
            ['enable_ai_resume', '1', 'boolean'],
            ['free_plan_max_applications', '10', 'integer'],
            ['basic_plan_price', '20000', 'integer'],
            ['premium_plan_price', '50000', 'integer'],
            ['enterprise_plan_price', '150000', 'integer'],
            ['smtp_host', 'smtp.gmail.com', 'string'],
            ['smtp_port', '587', 'integer'],
            ['smtp_username', 'noreply@skillsystem.rw', 'string'],
            ['smtp_password', '', 'string'],
            ['smtp_encryption', 'tls', 'string'],
            ['mail_from_address', 'noreply@skillsystem.rw', 'string'],
            ['mail_from_name', 'SkillSystem', 'string'],
            ['session_timeout', '120', 'integer'],
            ['password_min_length', '8', 'integer'],
            ['password_require_special', '0', 'boolean'],
            ['max_login_attempts', '5', 'integer'],
            ['enable_2fa', '0', 'boolean'],
            ['recaptcha_site_key', '', 'string'],
            ['recaptcha_secret_key', '', 'string'],
            ['social_facebook', 'https://facebook.com/skillsystem', 'string'],
            ['social_twitter', 'https://twitter.com/skillsystem', 'string'],
            ['social_linkedin', 'https://linkedin.com/company/skillsystem', 'string'],
            ['social_instagram', 'https://instagram.com/skillsystem', 'string'],
            ['social_youtube', 'https://youtube.com/@skillsystem', 'string'],
            ['social_whatsapp', '+250788000001', 'string'],
            ['social_telegram', '', 'string'],
            ['social_github', 'https://github.com/skillsystem', 'string'],
            ['google_analytics_id', '', 'string'],
            ['facebook_pixel_id', '', 'string'],
            ['currency', 'RWF', 'string'],
            ['payment_provider', '', 'string'],
            ['stripe_public_key', '', 'string'],
            ['stripe_secret_key', '', 'string'],
            ['paypal_client_id', '', 'string'],
            ['paypal_client_secret', '', 'string'],
            ['subscription_price', '20000', 'integer'],
        ];

        foreach ($defaults as $d) {
            $settingsModel->execute(
                "INSERT IGNORE INTO settings (`key`, `value`, `type`) VALUES (?, ?, ?)",
                $d
            );
        }

        // Load all settings
        $settings = $settingsModel->all('key', 'ASC');

        $this->view('admin/settings', ['settings' => $settings]);
    }

    public function updateSettings(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('admin/settings');
            return;
        }

        $settingsModel = new BaseModel();
        $settingsModel->setTable('settings');

        // Get all current settings so we know which ones are boolean (switches)
        $allSettings = $settingsModel->all('key', 'ASC');
        $booleanKeys = [];
        foreach ($allSettings as $s) {
            if ($s['type'] === 'boolean') {
                $booleanKeys[] = $s['key'];
            }
        }

        // First, set all boolean settings to '0' (unchecked checkboxes don't send values)
        foreach ($booleanKeys as $bKey) {
            $settingsModel->execute(
                "UPDATE settings SET value = '0' WHERE `key` = ?",
                [$bKey]
            );
        }

        // Then process all submitted fields (checked switches will override to '1')
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = str_replace('setting_', '', $key);
                // Use INSERT ... ON DUPLICATE KEY UPDATE so it works even if the row doesn't exist
                $settingsModel->execute(
                    "INSERT INTO settings (`key`, `value`, `type`) VALUES (?, ?, 'string') ON DUPLICATE KEY UPDATE value = ?",
                    [$settingKey, $value, $value]
                );
            }
        }

        $this->adminModel->logAudit(Session::userId(), 'update', 'settings', null, null, ['updated' => 'multiple']);
        Flash::success('Settings updated successfully.');
        $this->redirect('admin/settings');
    }

    public function messages(): void
    {
        $base = new BaseModel();
        $messages = $base->query("
            SELECT m.*, s.first_name as sender_first, s.last_name as sender_last,
                   r.first_name as receiver_first, r.last_name as receiver_last
            FROM messages m
            LEFT JOIN users s ON m.sender_id = s.id
            LEFT JOIN users r ON m.receiver_id = r.id
            ORDER BY m.created_at DESC LIMIT 100
        ");
        $unread = 0;
        foreach ($messages as $msg) {
            if (empty($msg['read_at'])) $unread++;
        }
        $this->view('admin/messages', [
            'messages' => $messages,
            'unread' => $unread,
            'total' => count($messages),
        ]);
    }

    public function notifications(): void
    {
        $base = new BaseModel();
        $notifications = $base->query("
            SELECT n.*, u.first_name, u.last_name
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.id
            ORDER BY n.created_at DESC LIMIT 100
        ");
        $unread = 0;
        foreach ($notifications as $n) {
            if (empty($n['read_at'])) $unread++;
        }
        $this->view('admin/notifications', [
            'notifications' => $notifications,
            'unread' => $unread,
            'total' => count($notifications),
        ]);
    }

    public function emailSms(): void
    {
        $settingsModel = new BaseModel();
        $settingsModel->setTable('settings');
        $settings = $settingsModel->all('key', 'ASC');
        $this->view('admin/email-sms', ['settings' => $settings]);
    }

    public function maintenance(): void
    {
        $settingsModel = new BaseModel();
        $settingsModel->setTable('settings');
        $settings = $settingsModel->all('key', 'ASC');
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache',
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'disk_free' => function_exists('disk_free_space') ? @disk_free_space('.') : false,
            'disk_total' => function_exists('disk_total_space') ? @disk_total_space('.') : false,
        ];
        $this->view('admin/maintenance', [
            'settings' => $settings,
            'systemInfo' => $systemInfo,
        ]);
    }

    public function auditLogs(): void
    {
        $page = $this->getInt('page', 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $logs = $this->adminModel->getRecentAuditLogs($perPage);
        $total = (int) (new BaseModel())->query("SELECT COUNT(*) as c FROM audit_logs")[0]['c'];

        $this->view('admin/audit-logs', [
            'logs' => $logs,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage)
        ]);
    }

    public function deleteJob(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $this->jobModel->delete($id);
        $this->adminModel->logAudit(Session::userId(), 'delete', 'jobs', $id, null, null);
        $this->json(['success' => true, 'message' => 'Job deleted']);
    }

    /**
     * Full job edit from the admin panel (title, description, location, type, salary, deadline, status).
     */
    public function updateJob(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('admin/jobs');
            return;
        }

        $job = $this->jobModel->find($id);
        if (!$job) {
            Flash::error('Job not found.');
            $this->redirect('admin/jobs');
            return;
        }

        $allowedStatuses = ['draft', 'published', 'closed', 'archived'];
        $status = $this->post('status');
        if (!in_array($status, $allowedStatuses, true)) {
            $status = $job['status'];
        }

        $this->jobModel->update($id, [
            'title' => $this->post('title') ?: $job['title'],
            'description' => $this->post('description') ?: $job['description'],
            'location' => $this->post('location') ?: $job['location'],
            'type' => $this->post('type') ?: $job['type'],
            'salary_min' => $this->getInt('salary_min') ?: null,
            'salary_max' => $this->getInt('salary_max') ?: null,
            'deadline' => $this->post('deadline') ?: $job['deadline'],
            'status' => $status,
        ]);

        $this->adminModel->logAudit(Session::userId(), 'update', 'jobs', $id, null, ['title' => $this->post('title')]);
        $this->logActivity('admin', 'Updated job: ' . $this->post('title'));
        Flash::success('Job updated successfully.');
        $this->redirect('admin/jobs');
    }

    public function deleteInternship(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $this->internModel->delete($id);
        $this->adminModel->logAudit(Session::userId(), 'delete', 'internships', $id, null, null);
        $this->json(['success' => true, 'message' => 'Internship deleted']);
    }

    /**
     * Full internship edit from the admin panel.
     */
    public function updateInternship(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('admin/internships');
            return;
        }

        $internship = $this->internModel->find($id);
        if (!$internship) {
            Flash::error('Internship not found.');
            $this->redirect('admin/internships');
            return;
        }

        $allowedStatuses = ['draft', 'published', 'closed', 'archived'];
        $status = $this->post('status');
        if (!in_array($status, $allowedStatuses, true)) {
            $status = $internship['status'];
        }

        $this->internModel->update($id, [
            'title' => $this->post('title') ?: $internship['title'],
            'description' => $this->post('description') ?: $internship['description'],
            'location' => $this->post('location') ?: $internship['location'],
            'duration' => $this->getInt('duration') ?: $internship['duration'],
            'duration_unit' => $this->post('duration_unit') ?: $internship['duration_unit'],
            'allowance' => $this->getInt('allowance') ?: 0,
            'positions_available' => $this->getInt('positions') ?: $internship['positions_available'],
            'deadline' => $this->post('deadline') ?: $internship['deadline'],
            'status' => $status,
        ]);

        $this->adminModel->logAudit(Session::userId(), 'update', 'internships', $id, null, ['title' => $this->post('title')]);
        $this->logActivity('admin', 'Updated internship: ' . $this->post('title'));
        Flash::success('Internship updated successfully.');
        $this->redirect('admin/internships');
    }

    public function verifyCertificate(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $base = new BaseModel();
        $base->execute("UPDATE certificates SET verified = 1 WHERE id = ?", [$id]);
        $this->adminModel->logAudit(Session::userId(), 'update', 'certificates', $id, null, ['verified' => 1]);
        $this->json(['success' => true, 'message' => 'Certificate verified']);
    }

    public function deleteCertificate(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $base = new BaseModel();
        $base->execute("DELETE FROM certificates WHERE id = ?", [$id]);
        $this->adminModel->logAudit(Session::userId(), 'delete', 'certificates', $id, null, null);
        $this->json(['success' => true, 'message' => 'Certificate deleted']);
    }

    public function refundPayment(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $base = new BaseModel();
        $base->execute("UPDATE payments SET status = 'refunded' WHERE id = ?", [$id]);
        $this->adminModel->logAudit(Session::userId(), 'update', 'payments', $id, null, ['status' => 'refunded']);
        $this->json(['success' => true, 'message' => 'Payment refunded']);
    }

    public function deleteNotification(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $base = new BaseModel();
        $base->execute("DELETE FROM notifications WHERE id = ?", [$id]);
        $this->json(['success' => true, 'message' => 'Notification deleted']);
    }

    // ============================================================
    // HOMEPAGE CONTENT MANAGEMENT
    // ============================================================

    public function homepageManager(): void
    {
        $base = new BaseModel();
        $base->setTable('homepage_content');
        $content = $base->query("SELECT * FROM homepage_content ORDER BY section ASC, sort_order ASC");

        // Group by section
        $sections = ['hero' => [], 'announcement' => [], 'video' => [], 'event' => [], 'testimonial' => [], 'custom' => []];
        foreach ($content as $row) {
            $sec = $row['section'] ?? 'custom';
            if (!isset($sections[$sec])) $sec = 'custom';
            $sections[$sec][] = $row;
        }

        $this->view('admin/homepage', ['sections' => $sections, 'allContent' => $content]);
    }

    public function addHomepageContent(): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('admin/homepage'); return; }

        // FIX: Validate section — previously a duplicate hidden input named "section"
        // was overriding the select value with an empty string, so all posts were
        // saved with section="" and never appeared on the homepage.
        $section = $this->post('section');
        $allowedSections = ['hero', 'announcement', 'video', 'event', 'testimonial', 'custom'];
        if (!in_array($section, $allowedSections, true)) {
            Flash::error('Invalid section selected. Please choose a valid section.');
            $this->redirect('admin/homepage');
            return;
        }

        $base = new BaseModel();
        $base->execute(
            "INSERT INTO homepage_content (section, title, subtitle, body, image_url, video_url, link_url, link_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $section,
                $this->post('title'),
                $this->post('subtitle'),
                $this->post('body'),
                $this->post('image_url'),
                $this->post('video_url'),
                $this->post('link_url'),
                $this->post('link_text'),
                (int)$this->post('sort_order') ?: 0,
                !empty($_POST['is_active']) ? 1 : 0,
            ]
        );

        $this->adminModel->logAudit(Session::userId(), 'create', 'homepage_content', null, null, ['section' => $section]);
        Flash::success('Homepage content added successfully.');
        $this->redirect('admin/homepage');
    }

    public function updateHomepageContent(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('admin/homepage'); return; }

        $base = new BaseModel();
        $base->execute(
            "UPDATE homepage_content SET section = ?, title = ?, subtitle = ?, body = ?, image_url = ?, video_url = ?, link_url = ?, link_text = ?, sort_order = ?, is_active = ? WHERE id = ?",
            [
                $this->post('section'),
                $this->post('title'),
                $this->post('subtitle'),
                $this->post('body'),
                $this->post('image_url'),
                $this->post('video_url'),
                $this->post('link_url'),
                $this->post('link_text'),
                (int)$this->post('sort_order') ?: 0,
                !empty($_POST['is_active']) ? 1 : 0,
                $id,
            ]
        );

        $this->adminModel->logAudit(Session::userId(), 'update', 'homepage_content', $id, null, null);
        Flash::success('Homepage content updated successfully.');
        $this->redirect('admin/homepage');
    }

    public function deleteHomepageContent(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('admin/homepage'); return; }

        $base = new BaseModel();
        $base->execute("DELETE FROM homepage_content WHERE id = ?", [$id]);

        $this->adminModel->logAudit(Session::userId(), 'delete', 'homepage_content', $id, null, null);
        Flash::success('Homepage content deleted.');
        $this->redirect('admin/homepage');
    }
}