<?php

namespace App\Controllers;

use App\Models\JobModel;
use App\Models\InternshipModel;
use App\Models\FreelanceModel;
use App\Models\StudentModel;
use App\Models\UserModel;

class HomeController extends BaseController
{
    public function index(): void
    {
        $jobModel = new JobModel();
        $internModel = new InternshipModel();
        $freelanceModel = new FreelanceModel();
        $userModel = new UserModel();

        // Featured jobs / internships / freelance
        $featuredJobs = $jobModel->getPublished(1, 6);
        $featuredInternships = $internModel->getPublished(1, 6);
        $featuredFreelance = $freelanceModel->getOpen(1, 4);

        if (!is_array($featuredJobs) || !isset($featuredJobs['data'])) {
            $featuredJobs = ['data' => [], 'total' => 0];
        }
        if (!is_array($featuredInternships) || !isset($featuredInternships['data'])) {
            $featuredInternships = ['data' => [], 'total' => 0];
        }
        if (!is_array($featuredFreelance) || !isset($featuredFreelance['data'])) {
            $featuredFreelance = ['data' => [], 'total' => 0];
        }

        $stats = [
            'students' => $userModel->countByRoleSafe('student'),
            'employers' => $userModel->countByRoleSafe('employer'),
            'jobs' => $jobModel->countPublished(),
            'internships' => $internModel->countPublished(),
        ];

        // Companies
        try {
            $companyModel = new \App\Models\BaseModel();
            $companyModel->setTable('companies');
            $companies = $companyModel->query("SELECT * FROM companies WHERE verified = 1 ORDER BY name LIMIT 12");
            if (!is_array($companies)) $companies = [];
        } catch (\Throwable $e) {
            $companies = [];
        }

        // Universities
        try {
            $unis = (new \App\Models\BaseModel())->query("SELECT * FROM universities ORDER BY total_students DESC LIMIT 8");
            if (!is_array($unis)) $unis = [];
        } catch (\Throwable $e) {
            $unis = [];
        }

        // Featured students (top profiles by completion)
        try {
            $students = (new \App\Models\BaseModel())->query("
                SELECT s.id, s.user_id, s.department, s.profile_completion, s.bio,
                       u.first_name, u.last_name, u.avatar
                FROM students s
                JOIN users u ON u.id = s.user_id
                WHERE u.status = 'active' AND s.profile_completion >= 70
                ORDER BY s.profile_completion DESC LIMIT 8
            ");
            if (!is_array($students)) $students = [];
        } catch (\Throwable $e) {
            $students = [];
        }

        // Community Members — ALL active users EXCEPT admins, with their name, email, phone, avatar, and role.
        // This powers the "Community Members" section on the homepage so anyone visiting
        // can see everyone who's registered in the system. Admins are excluded for security/privacy.
        $members = [];
        $memberCount = 0;
        try {
            $members = (new \App\Models\BaseModel())->query("
                SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.avatar,
                       u.role_id, r.slug AS role_slug, r.name AS role_name
                FROM users u
                LEFT JOIN roles r ON r.id = u.role_id
                WHERE u.status = 'active' AND (r.slug IS NULL OR r.slug != 'admin')
                ORDER BY u.created_at DESC
            ");
            if (!is_array($members)) $members = [];
            $memberCount = count($members);
        } catch (\Throwable $e) {
            $members = [];
        }

        // Testimonials (static for now, since no testimonials table)
        $testimonials = [
            ['name' => 'Jean Pierre Habarugira', 'role' => 'Software Engineering Student, UR', 'avatar' => 'J', 'rating' => 5, 'text' => 'SkillSystem helped me land my dream internship at Bank of Kigali within 3 weeks. The AI resume score was a game changer!'],
            ['name' => 'Grace Uwimana', 'role' => 'Computer Science Graduate, UR', 'avatar' => 'G', 'rating' => 5, 'text' => 'The portfolio feature got me noticed by 3 top companies. I received multiple interview invites in my first month.'],
            ['name' => 'Patrick Mugisha', 'role' => 'HR Manager, RwandaTech', 'avatar' => 'P', 'rating' => 5, 'text' => 'As an employer, SkillSystem streamlined our hiring process. The candidate filtering and AI scoring saved us countless hours.'],
            ['name' => 'Sarah Mukantwari', 'role' => 'Business Student, ALU', 'avatar' => 'S', 'rating' => 4, 'text' => 'The career roadmap feature gave me clarity on what skills to learn next. I went from 0 to employed in 6 months.'],
            ['name' => 'Prof. Jean Kagame', 'role' => 'Career Center Director, UR', 'avatar' => 'J', 'rating' => 5, 'text' => 'For universities, the analytics and student tracking are invaluable. We can finally measure real career outcomes.'],
            ['name' => 'Eric Niyonzima', 'role' => 'Data Analyst, MTN Rwanda', 'avatar' => 'E', 'rating' => 5, 'text' => 'The verified certificates and QR code verification made my credentials trustworthy. Recruiters loved it.'],
        ];

        // FAQ items
        $faqs = [
            ['q' => 'Is SkillSystem free for students?', 'a' => 'Yes! SkillSystem is completely free for students. You can build your portfolio, apply to jobs and internships, and access all career tools at no cost.'],
            ['q' => 'How does the AI Resume Score work?', 'a' => 'Our rule-based AI analyzes your profile completeness, skills, experience, portfolio, and certificates to generate a 0-100 score with personalized suggestions for improvement.'],
            ['q' => 'Can employers post jobs for free?', 'a' => 'Employers can post up to 5 jobs per month for free. Premium plans are available for unlimited postings, advanced analytics, and candidate matching.'],
            ['q' => 'Are certificates verifiable?', 'a' => 'Yes! Every certificate issued through SkillSystem gets a unique verification code and QR code. Employers can verify authenticity by scanning the QR or entering the code on our verification page.'],
            ['q' => 'How do I get matched with relevant jobs?', 'a' => 'Our algorithm matches you based on your skills, department, year of study, and preferences. The more complete your profile, the better the matches.'],
            ['q' => 'Can universities track student career outcomes?', 'a' => 'Yes. University accounts get access to dashboards showing student placement rates, employer partnerships, internship statistics, and graduate tracking.'],
        ];

        // Career resources
        $resources = [
            ['icon' => 'fa-file-alt', 'title' => 'Resume Builder', 'desc' => 'Create a professional resume with our step-by-step builder. ATS-friendly templates.', 'color' => 'primary'],
            ['icon' => 'fa-robot', 'title' => 'AI Career Coach', 'desc' => 'Get personalized career recommendations and skill gap analysis powered by AI.', 'color' => 'info'],
            ['icon' => 'fa-road', 'title' => 'Career Roadmap', 'desc' => 'Visualize your path from student to professional with milestone tracking.', 'color' => 'success'],
            ['icon' => 'fa-comments', 'title' => 'Mentorship', 'desc' => 'Connect with industry mentors for 1-on-1 guidance and career advice.', 'color' => 'warning'],
            ['icon' => 'fa-trophy', 'title' => 'Leaderboard', 'desc' => 'Compete with peers, earn badges, and climb the rankings as you grow.', 'color' => 'accent'],
            ['icon' => 'fa-calendar-alt', 'title' => 'Events & Workshops', 'desc' => 'Attend career fairs, workshops, and webinars from top employers.', 'color' => 'secondary'],
        ];

        // Load dynamic homepage content from database
        $homepageContent = ['hero' => null, 'announcements' => [], 'videos' => [], 'events' => [], 'testimonials' => []];
        try {
            $base = new \App\Models\BaseModel();

            // Auto-create homepage_content table if it doesn't exist
            $base->execute("CREATE TABLE IF NOT EXISTS `homepage_content` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `section` VARCHAR(50) NOT NULL,
                `title` VARCHAR(255) DEFAULT NULL,
                `subtitle` VARCHAR(500) DEFAULT NULL,
                `body` TEXT DEFAULT NULL,
                `image_url` VARCHAR(500) DEFAULT NULL,
                `video_url` VARCHAR(500) DEFAULT NULL,
                `link_url` VARCHAR(500) DEFAULT NULL,
                `link_text` VARCHAR(100) DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Seed default content if table is empty
            $count = $base->queryOne("SELECT COUNT(*) as c FROM homepage_content");
            if ((int)($count['c'] ?? 0) === 0) {
                $defaults = [
                    ['announcement', 'Welcome to SkillSystem v3.0!', 'Our new premium dashboard is now live with AI insights, real-time analytics, and a beautiful new design.', 'Check out the new features today!', '', '', '', '', 1, 1],
                    ['announcement', 'Career Fair 2025 — Registration Open', 'Join us at the Kigali Convention Centre on February 15, 2025. Meet 50+ employers hiring students.', 'Register now to secure your spot!', '', '', '', '', 2, 1],
                    ['video', 'How SkillSystem Works', 'Watch this 2-minute overview of how SkillSystem connects students with employers.', '', '', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '', '', 1, 1],
                    ['event', 'SkillSystem Career Fair 2025', 'Annual career fair connecting students with top employers in Rwanda.', 'Kigali Convention Centre', '', '', '', 'Register Now', 1, 1],
                    ['event', 'AI for Rwanda Hackathon', '48-hour hackathon building AI solutions for Rwanda.', 'University of Rwanda - CBE Campus', '', '', '', 'Learn More', 2, 1],
                    ['event', 'Resume Writing Workshop', 'Interactive workshop on writing effective resumes and cover letters.', 'Online (Zoom)', '', '', '', 'Join Workshop', 3, 1],
                ];
                foreach ($defaults as $d) {
                    $base->execute(
                        "INSERT INTO homepage_content (section, title, subtitle, body, image_url, video_url, link_url, link_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        $d
                    );
                }
            }

            // Load active content
            $rows = $base->query("SELECT * FROM homepage_content WHERE is_active = 1 ORDER BY sort_order ASC");
            foreach ($rows as $row) {
                $sec = $row['section'] ?? 'custom';
                // FIX: If section is empty or 'custom', try to infer it from the content
                // so that posts saved with the previous bug (empty section) still display.
                if (empty($sec) || $sec === 'custom') {
                    if (!empty($row['video_url'])) {
                        $sec = 'video';
                    } elseif (!empty($row['image_url']) && stripos($row['title'] ?? '', 'testimonial') !== false) {
                        $sec = 'testimonial';
                    } else {
                        // Default to announcement so the post is at least visible
                        $sec = 'announcement';
                    }
                }
                if ($sec === 'hero' && !$homepageContent['hero']) {
                    $homepageContent['hero'] = $row;
                } elseif ($sec === 'announcement') {
                    $homepageContent['announcements'][] = $row;
                } elseif ($sec === 'video') {
                    $homepageContent['videos'][] = $row;
                } elseif ($sec === 'event') {
                    $homepageContent['events'][] = $row;
                } elseif ($sec === 'testimonial') {
                    $homepageContent['testimonials'][] = $row;
                }
            }
        } catch (\Throwable $e) {}

        $this->view('home/index', [
            'featuredJobs' => $featuredJobs,
            'featuredInternships' => $featuredInternships,
            'featuredFreelance' => $featuredFreelance,
            'stats' => $stats,
            'companies' => $companies,
            'universities' => $unis,
            'students' => $students,
            'members' => $members,
            'memberCount' => $memberCount,
            'testimonials' => $testimonials,
            'faqs' => $faqs,
            'resources' => $resources,
            'homepageContent' => $homepageContent,
        ]);
    }


    /**
     * Setup / Diagnostic page — checks DB connection, tables, and config.
     * Accessible at /setup even if the database is not configured.
     */
    public function setup(): void
    {
        $checks = [];
        $allPass = true;

        // 1. PHP version check
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.0.0', '>=');
        $checks[] = ['label' => 'PHP version >= 8.0', 'status' => $phpOk, 'detail' => 'Found: ' . $phpVersion];
        if (!$phpOk) $allPass = false;

        // 2. PDO extension
        $pdoOk = extension_loaded('pdo_mysql');
        $checks[] = ['label' => 'PDO MySQL extension', 'status' => $pdoOk, 'detail' => $pdoOk ? 'Loaded' : 'NOT loaded — enable in php.ini'];
        if (!$pdoOk) $allPass = false;

        // 3. .env file
        $envExists = file_exists(ROOT_PATH . '/.env');
        $checks[] = ['label' => '.env file exists', 'status' => $envExists, 'detail' => $envExists ? 'Found' : 'Missing — copy .env.example to .env'];
        if (!$envExists) $allPass = false;

        // 4. Database connection
        $dbConnected = false;
        $dbError = '';
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }
        $checks[] = ['label' => 'Database connection', 'status' => $dbConnected, 'detail' => $dbConnected ? 'Connected to ' . DB_NAME : 'FAILED: ' . $dbError];
        if (!$dbConnected) $allPass = false;

        // 5. Required tables
        $requiredTables = ['users', 'roles', 'jobs', 'internships', 'applications', 'students', 'employers', 'notifications', 'messages', 'audit_logs'];
        $existingTables = [];
        if ($dbConnected) {
            try {
                $stmt = $db->query("SHOW TABLES");
                $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $existingTables = $allTables;
            } catch (\Throwable $e) {
                $existingTables = [];
            }
        }

        foreach ($requiredTables as $tbl) {
            $exists = in_array($tbl, $existingTables);
            $checks[] = ['label' => "Table: {$tbl}", 'status' => $exists, 'detail' => $exists ? 'Exists' : 'MISSING — import database/skillsystem.sql'];
            if (!$exists) $allPass = false;
        }

        // 6. Admin user check
        $adminOk = false;
        if ($dbConnected && in_array('users', $existingTables)) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'admin'");
                $stmt->execute();
                $adminCount = (int) $stmt->fetchColumn();
                $adminOk = $adminCount > 0;
                $checks[] = ['label' => 'Admin user exists', 'status' => $adminOk, 'detail' => $adminOk ? "{$adminCount} admin(s) found" : 'No admin user — import skillsystem.sql'];
            } catch (\Throwable $e) {
                $checks[] = ['label' => 'Admin user exists', 'status' => false, 'detail' => 'Could not query: ' . $e->getMessage()];
            }
        }
        if (!$adminOk) $allPass = false;

        // Render the setup page
        $pageTitle = 'Setup Check - ' . APP_NAME;

        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($pageTitle) . '</title>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
        echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">';
        echo '<style>';
        echo '*{margin:0;padding:0;box-sizing:border-box}';
        echo 'body{font-family:Poppins,sans-serif;background:linear-gradient(135deg,#062418 0%,#0d2a1f 50%,#1a1f0a 100%);min-height:100vh;padding:24px;color:#f1f5f9}';
        echo '.wrap{max-width:800px;margin:0 auto}';
        echo '.header{text-align:center;margin-bottom:32px}';
        echo '.logo{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#059669,#f59e0b);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:#fff}';
        echo 'h1{font-size:28px;font-weight:800;margin-bottom:8px}';
        echo '.subtitle{color:rgba(255,255,255,0.6);font-size:14px}';
        echo '.summary{background:' . ($allPass ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)') . ';border:1px solid ' . ($allPass ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)') . ';border-radius:14px;padding:20px;margin-bottom:24px;text-align:center}';
        echo '.summary i{font-size:32px;color:' . ($allPass ? '#22c55e' : '#ef4444') . ';margin-bottom:8px}';
        echo '.summary h2{font-size:18px;font-weight:700;margin-bottom:4px}';
        echo '.summary p{font-size:13px;color:rgba(255,255,255,0.6)}';
        echo '.card{background:#1e293b;border:1px solid #334155;border-radius:14px;overflow:hidden;margin-bottom:24px}';
        echo '.card-header{padding:16px 24px;border-bottom:1px solid #334155;font-size:15px;font-weight:700}';
        echo '.check{display:flex;align-items:center;gap:14px;padding:14px 24px;border-bottom:1px solid rgba(255,255,255,0.05)}';
        echo '.check:last-child{border-bottom:none}';
        echo '.check-icon{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}';
        echo '.check-pass{background:rgba(34,197,94,0.15);color:#22c55e}';
        echo '.check-fail{background:rgba(239,68,68,0.15);color:#ef4444}';
        echo '.check-label{font-size:14px;font-weight:600;flex:1}';
        echo '.check-detail{font-size:12px;color:rgba(255,255,255,0.5);font-family:monospace;max-width:400px;word-break:break-all;text-align:right}';
        echo '.actions{display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap}';
        echo '.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:600;font-size:14px;transition:all .2s}';
        echo '.btn-primary{background:#059669;color:#fff}';
        echo '.btn-primary:hover{background:#047857;transform:translateY(-1px)}';
        echo '.btn-secondary{background:#1e293b;border:1px solid #334155;color:#f1f5f9}';
        echo '.btn-secondary:hover{background:#334155}';
        echo '.info{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:20px;margin-bottom:24px;font-size:13px;line-height:1.8;color:rgba(255,255,255,0.7)}';
        echo '.info strong{color:#f1f5f9}';
        echo '.info code{background:#0f172a;padding:2px 8px;border-radius:4px;font-size:12px;color:#fbbf24}';
        echo '</style></head><body>';
        echo '<div class="wrap">';

        // Header
        echo '<div class="header">';
        echo '<div class="logo"><i class="fas fa-graduation-cap"></i></div>';
        echo '<h1>SkillSystem Setup Check</h1>';
        echo '<p class="subtitle">Diagnosing your installation to find why the page is blank</p>';
        echo '</div>';

        // Summary
        if ($allPass) {
            echo '<div class="summary"><i class="fas fa-check-circle"></i><h2>All checks passed!</h2><p>Your SkillSystem is properly configured. You can now visit the home page.</p></div>';
        } else {
            echo '<div class="summary"><i class="fas fa-exclamation-triangle"></i><h2>Some checks failed</h2><p>Fix the items marked with ✗ below, then refresh this page.</p></div>';
        }

        // Checks
        echo '<div class="card"><div class="card-header"><i class="fas fa-list-check me-2"></i> Diagnostic Checks</div>';
        foreach ($checks as $check) {
            $icon = $check['status'] ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
            $cls = $check['status'] ? 'check-pass' : 'check-fail';
            echo "<div class='check'><div class='check-icon {$cls}'>{$icon}</div>";
            echo "<div class='check-label'>" . htmlspecialchars($check['label']) . "</div>";
            echo "<div class='check-detail'>" . htmlspecialchars($check['detail']) . "</div></div>";
        }
        echo '</div>';

        // Instructions
        echo '<div class="info">';
        echo '<strong><i class="fas fa-info-circle"></i> How to fix common issues:</strong><br><br>';
        echo '<strong>1. Database not connected?</strong><br>';
        echo 'Open phpMyAdmin (<code>http://localhost/phpmyadmin</code>) and create a new database named <code>skillsystem</code>.<br><br>';
        echo '<strong>2. Tables missing?</strong><br>';
        echo 'In phpMyAdmin, select the <code>skillsystem</code> database, click the <strong>Import</strong> tab, choose <code>database/skillsystem.sql</code>, and click Go.<br><br>';
        echo '<strong>3. Wrong DB credentials?</strong><br>';
        echo 'Edit the <code>.env</code> file in the project root. On XAMPP, the defaults are: DB_USER=root, DB_PASS= (empty).<br><br>';
        echo '<strong>4. Admin login:</strong><br>';
        echo 'Email: <code>ethiennemugisha35@gmail.com</code> &nbsp; Password: <code>password</code>';
        echo '</div>';

        // Actions
        echo '<div class="actions">';
        echo '<a href="' . APP_URL . '" class="btn btn-primary"><i class="fas fa-home"></i> Go to Home Page</a>';
        echo '<a href="' . APP_URL . '/setup" class="btn btn-secondary"><i class="fas fa-redo"></i> Re-run Setup Check</a>';
        echo '<a href="http://localhost/phpmyadmin/" class="btn btn-secondary"><i class="fas fa-database"></i> Open phpMyAdmin</a>';
        echo '</div>';

        echo '</div></body></html>';
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->view('home/404');
    }
}