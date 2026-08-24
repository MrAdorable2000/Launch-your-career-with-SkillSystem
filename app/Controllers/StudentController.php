<?php

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\Upload;
use App\Helpers\Validator;
use App\Models\StudentModel;
use App\Models\JobModel;
use App\Models\ApplicationModel;
use App\Models\InternshipModel;
use App\Models\FreelanceModel;
use App\Models\MessageModel;
use App\Models\NotificationModel;
use App\Models\BaseModel;
use App\Helpers\Session;
use App\Helpers\CSRF;
use App\Helpers\AiScorer;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class StudentController extends BaseController
{
    private StudentModel $studentModel;
    private JobModel $jobModel;
    private ApplicationModel $appModel;
    private MessageModel $msgModel;
    private NotificationModel $notifModel;

    public function __construct()
    {
        // CRITICAL: Enforce authentication + student role on student routes.
        // Note: API endpoints (getNotifications, markNotificationsRead, etc.) also
        // require auth — this is correct, they should never be public.
        AuthMiddleware::handle();
        RoleMiddleware::handle(['student']);

        $this->studentModel = new StudentModel();
        $this->jobModel = new JobModel();
        $this->appModel = new ApplicationModel();
        $this->msgModel = new MessageModel();
        $this->notifModel = new NotificationModel();
    }

    public function dashboard(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        $skills = $this->studentModel->getSkills($student['id'] ?? 0);
        $appStats = $this->appModel->getByUser(Session::userId());
        $stats = $this->studentModel->getApplicationStats(Session::userId());
        $recentApps = $this->appModel->getByUser(Session::userId(), 1, 5);
        $recentNotifs = $this->notifModel->getRecentForUser(Session::userId(), 5);

        // Monthly application data for chart
        $monthlyData = $this->appModel->query(
            "SELECT DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count
             FROM applications WHERE user_id = ? AND applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month",
            [Session::userId()]
        );

        // AI Resume Score
        $aiScore = null;
        $recommendations = [];
        $badges = [];
        if ($student) {
            try {
                $aiScore = \App\Helpers\AiScorer::resumeScore($student['id']);
                $recommendations = \App\Helpers\AiScorer::careerRecommendations(Session::userId(), $student['id'], 4);
                $badgeModel = new \App\Models\BadgeModel();
                $badgeModel->autoAward($student['id']);
                $badges = $badgeModel->getForStudent($student['id']);
            } catch (\Throwable $e) {
                // Silent fail
            }
        }

        // Portfolio count
        $portfolioCount = 0;
        if ($student) {
            $pc = $this->studentModel->queryOne("SELECT COUNT(*) as c FROM portfolios WHERE student_id = ?", [$student['id']]);
            $portfolioCount = (int)($pc['c'] ?? 0);
        }

        // Recommended internships (latest 3)
        $recInternships = $this->appModel->query(
            "SELECT i.*, e.company_name FROM internships i
             JOIN employers e ON e.id = i.employer_id
             WHERE i.status = 'published' AND i.deadline >= CURDATE()
             ORDER BY i.created_at DESC LIMIT 3"
        );

        // Activity timeline (recent activity_logs)
        $activities = $this->appModel->query(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 6",
            [Session::userId()]
        );

        $this->view('student/dashboard', [
            'student'           => $student,
            'skills'            => $skills,
            'stats'             => $stats,
            'recentApplications' => $recentApps['data'] ?? [],
            'monthlyData'       => $monthlyData,
            'recentNotifications' => $recentNotifs,
            'aiScore'           => $aiScore,
            'recommendations'   => $recommendations,
            'recInternships'    => $recInternships,
            'activities'        => $activities,
            'badges'            => $badges,
            'portfolioCount'    => $portfolioCount,
        ]);
    }

    public function profile(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        $skills = $this->studentModel->getSkills($student['id'] ?? 0);
        $education = $this->studentModel->getEducation($student['id'] ?? 0);
        $experience = $this->studentModel->getExperience($student['id'] ?? 0);
        $allSkills = (new BaseModel())->query("SELECT * FROM skills ORDER BY name");

        $this->view('student/profile', [
            'student' => $student,
            'skills' => $skills,
            'education' => $education,
            'experience' => $experience,
            'allSkills' => $allSkills
        ]);
    }

    public function updateProfile(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/profile');
            return;
        }

        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/profile');
            return;
        }

        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload = Upload::handle($_FILES['avatar'], 'uploads/avatars', ['jpg', 'jpeg', 'png', 'gif']);
            if ($upload['success']) {
                $this->studentModel->execute("UPDATE users SET avatar = ? WHERE id = ?", [$upload['path'], Session::userId()]);
                Session::set('userAvatar', $upload['path']);
            }
        }

        // Update student profile
        $this->studentModel->update($student['id'], [
            'bio' => $this->post('bio'),
            'department' => $this->post('department'),
            'year_of_study' => $this->getInt('year_of_study') ?: null,
            'gpa' => $this->post('gpa') ?: null,
            'linkedin' => $this->post('linkedin'),
            'github' => $this->post('github'),
            'website' => $this->post('website'),
            'skills_summary' => $this->post('skills_summary')
        ]);

        // Update user name
        $this->studentModel->execute(
            "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?",
            [$this->post('first_name'), $this->post('last_name'), $this->post('phone'), Session::userId()]
        );
        Session::set('userName', $this->post('first_name') . ' ' . $this->post('last_name'));

        // Handle skill additions
        $skillIds = $_POST['skills'] ?? [];
        if (!empty($skillIds)) {
            $this->studentModel->execute("DELETE FROM student_skills WHERE student_id = ?", [$student['id']]);
            foreach ($skillIds as $skillId) {
                $proficiency = $_POST['proficiency_' . $skillId] ?? 'intermediate';
                $this->studentModel->execute(
                    "INSERT INTO student_skills (student_id, skill_id, proficiency_level) VALUES (?, ?, ?)",
                    [$student['id'], (int)$skillId, $proficiency]
                );
            }
        }

        // Calculate profile completion
        $completion = 10; // base
        if ($this->post('bio')) $completion += 15;
        if ($this->post('department')) $completion += 10;
        if ($this->post('linkedin')) $completion += 5;
        if ($this->post('github')) $completion += 5;
        if (!empty($skillIds)) $completion += 15;
        $eduCount = $this->studentModel->query("SELECT COUNT(*) as c FROM education WHERE student_id = ?", [$student['id']]);
        if ($eduCount[0]['c'] > 0) $completion += 15;
        $expCount = $this->studentModel->query("SELECT COUNT(*) as c FROM experience WHERE student_id = ?", [$student['id']]);
        if ($expCount[0]['c'] > 0) $completion += 10;
        if (Session::get('userAvatar')) $completion += 5;
        $completion = min($completion, 100);
        $this->studentModel->update($student['id'], ['profile_completion' => $completion]);

        $this->logActivity('profile', 'Updated profile information');
        Flash::success('Profile updated successfully.');
        $this->redirect('student/profile');
    }

    public function jobs(): void
    {
        $page = $this->getInt('page', 1);
        $filters = [
            'search'   => $this->post('search') ?: ($_GET['search'] ?? ''),
            'type'     => $this->post('type') ?: ($_GET['type'] ?? ''),
            'location' => $this->post('location') ?: ($_GET['location'] ?? ''),
            'remote'   => $this->post('remote') ?: ($_GET['remote'] ?? '')
        ];

        $jobs = $this->jobModel->getPublished($page, 10, $filters);

        $this->view('student/jobs', [
            'jobs' => $jobs,
            'filters' => $filters
        ]);
    }

    public function viewJob(int $id): void
    {
        $job = $this->jobModel->getWithCompany($id);
        if (!$job) {
            Flash::error('Job not found.');
            $this->redirect('student/jobs');
            return;
        }
        $this->jobModel->incrementViews($id);
        $hasApplied = $this->appModel->hasApplied(Session::userId(), $id);

        $this->view('student/view-job', [
            'job' => $job,
            'hasApplied' => $hasApplied
        ]);
    }

    public function applyJob(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/jobs');
            return;
        }

        if ($this->appModel->hasApplied(Session::userId(), $id)) {
            Flash::warning('You have already applied for this job.');
            $this->redirect('student/jobs');
            return;
        }

        $job = $this->jobModel->find($id);
        if (!$job) {
            Flash::error('Job not found.');
            $this->redirect('student/jobs');
            return;
        }

        $coverLetter = $this->post('cover_letter');

        $appId = $this->appModel->create([
            'user_id' => Session::userId(),
            'job_id' => $id,
            'type' => 'job',
            'cover_letter' => $coverLetter,
            'status' => 'pending'
        ]);

        // Notify employer
        $employer = $this->studentModel->queryOne("SELECT user_id FROM employers WHERE id = ?", [$job['employer_id']]);
        if ($employer) {
            $userName = Session::userName();
            $this->notifModel->createNotification($employer['user_id'], 'application', 'New Job Application',
                "{$userName} applied for: {$job['title']}", ['application_id' => $appId]);
        }

        $this->logActivity('application', "Applied for job: {$job['title']}");
        Flash::success('Application submitted successfully!');
        $this->redirect('student/applications');
    }

    public function applications(): void
    {
        $page = $this->getInt('page', 1);
        $applications = $this->appModel->getByUser(Session::userId(), $page, 10);

        $this->view('student/applications', [
            'applications' => $applications
        ]);
    }

    public function portfolio(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        $portfolios = $this->studentModel->getPortfolios($student['id'] ?? 0);

        $this->view('student/portfolio', [
            'student' => $student,
            'portfolios' => $portfolios
        ]);
    }

    public function addPortfolio(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/portfolio');
            return;
        }

        $student = $this->studentModel->findByUserId(Session::userId());

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = Upload::handle($_FILES['image'], 'uploads/portfolios', ['jpg', 'jpeg', 'png', 'gif']);
            if ($upload['success']) {
                $imagePath = $upload['path'];
            }
        }

        (new BaseModel())->execute(
            "INSERT INTO portfolios (student_id, title, description, url, technologies, image) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $student['id'],
                $this->post('title'),
                $this->post('description'),
                $this->post('url'),
                $this->post('technologies'),
                $imagePath
            ]
        );

        $this->logActivity('portfolio', 'Added portfolio project: ' . $this->post('title'));
        Flash::success('Portfolio project added.');
        $this->redirect('student/portfolio');
    }

    public function resume(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        $resumes = $this->studentModel->query("SELECT * FROM resumes WHERE student_id = ? ORDER BY created_at DESC", [$student['id'] ?? 0]);

        $this->view('student/resume', [
            'student' => $student,
            'resumes' => $resumes
        ]);
    }

    public function messages(): void
    {
        $page = $this->getInt('page', 1);
        $inbox = $this->msgModel->getInbox(Session::userId(), $page, 15);

        $this->view('student/messages', [
            'inbox' => $inbox
        ]);
    }

    public function settings(): void
    {
        // Fetch the current user's data so the Account Info form can be pre-populated
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(Session::userId());

        $this->view('student/settings', [
            'user' => $user,
        ]);
    }

    public function updateSettings(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/settings');
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        if (!empty($newPassword)) {
            $user = $this->studentModel->queryOne("SELECT password FROM users WHERE id = ?", [Session::userId()]);
            if (!password_verify($currentPassword, $user['password'])) {
                Flash::error('Current password is incorrect.');
                $this->redirect('student/settings');
                return;
            }
            if ($newPassword !== $confirmPassword) {
                Flash::error('New passwords do not match.');
                $this->redirect('student/settings');
                return;
            }
            if (strlen($newPassword) < 8) {
                Flash::error('New password must be at least 8 characters.');
                $this->redirect('student/settings');
                return;
            }
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->studentModel->execute("UPDATE users SET password = ? WHERE id = ?", [$hashed, Session::userId()]);
            $this->logActivity('settings', 'Password changed');
        }

        Flash::success('Settings updated successfully.');
        $this->redirect('student/settings');
    }

        // ---- Add these methods to StudentController.php ----

    /**
     * AJAX: Get notification count for polling
     */
    public function getNotifications(): void
    {
        $this->json(['count' => $this->notifModel->getUnreadCount(Session::userId())]);
    }

    /**
     * AJAX: Mark notifications as read
     */
    public function markNotificationsRead(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);

        if ($id > 0) {
            $this->notifModel->markAsRead($id, Session::userId());
        }

        $this->json(['success' => true, 'unread' => $this->notifModel->getUnreadCount(Session::userId())]);
    }

    /**
     * AJAX: Get unread message count
     */
    public function getUnreadCount(): void
    {
        $this->json(['count' => $this->msgModel->getUnreadCount(Session::userId())]);
    }

    public function withdrawApplication(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/applications'); return; }
        $base = new \App\Models\BaseModel();
        $app = $base->queryOne("SELECT * FROM applications WHERE id = ? AND user_id = ?", [$id, Session::userId()]);
        if (!$app) { Flash::error('Application not found.'); $this->redirect('student/applications'); return; }
        $base->execute("UPDATE applications SET status = 'withdrawn' WHERE id = ?", [$id]);
        $this->logActivity('application', 'Withdrew application #' . $id);
        Flash::success('Application withdrawn successfully.');
        $this->redirect('student/applications');
    }

    public function deletePortfolio(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/portfolio'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { Flash::error('Profile not found.'); $this->redirect('student/portfolio'); return; }
        $base = new \App\Models\BaseModel();
        $portfolio = $base->queryOne("SELECT * FROM portfolios WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        if (!$portfolio) { Flash::error('Portfolio not found.'); $this->redirect('student/portfolio'); return; }
        if (!empty($portfolio['image'])) { \App\Helpers\Upload::delete($portfolio['image']); }
        $base->execute("DELETE FROM portfolios WHERE id = ?", [$id]);
        $this->logActivity('portfolio', 'Deleted portfolio: ' . ($portfolio['title'] ?? ''));
        Flash::success('Portfolio project deleted.');
        $this->redirect('student/portfolio');
    }

    public function uploadResume(): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/resume'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { Flash::error('Profile not found.'); $this->redirect('student/resume'); return; }
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) { Flash::error('Please select a file.'); $this->redirect('student/resume'); return; }
        $upload = \App\Helpers\Upload::handle($_FILES['resume'], 'uploads/resumes', ['pdf', 'doc', 'docx']);
        if (!$upload['success']) { Flash::error($upload['message']); $this->redirect('student/resume'); return; }
        $title = $this->post('title') ?: 'My Resume';
        $base = new \App\Models\BaseModel();
        $base->execute("INSERT INTO resumes (student_id, title, file_path, is_default) VALUES (?, ?, ?, 0)", [$student['id'], $title, $upload['path']]);
        $this->logActivity('resume', 'Uploaded resume: ' . $title);
        Flash::success('Resume uploaded successfully.');
        $this->redirect('student/resume');
    }

    public function downloadResume(int $id): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/resume'); return; }
        $base = new \App\Models\BaseModel();
        $resume = $base->queryOne("SELECT * FROM resumes WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        if (!$resume || empty($resume['file_path'])) { Flash::error('Resume not found.'); $this->redirect('student/resume'); return; }
        $filePath = ROOT_PATH . '/public/assets/' . $resume['file_path'];
        if (!file_exists($filePath)) { Flash::error('File not found on disk.'); $this->redirect('student/resume'); return; }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($resume['file_path']) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function deleteResume(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/resume'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/resume'); return; }
        $base = new \App\Models\BaseModel();
        $resume = $base->queryOne("SELECT * FROM resumes WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        if (!$resume) { Flash::error('Resume not found.'); $this->redirect('student/resume'); return; }
        if (!empty($resume['file_path'])) { \App\Helpers\Upload::delete($resume['file_path']); }
        $base->execute("DELETE FROM resumes WHERE id = ?", [$id]);
        Flash::success('Resume deleted.');
        $this->redirect('student/resume');
    }

    public function addEducation(): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/profile'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/profile'); return; }
        $base = new \App\Models\BaseModel();
        $base->execute("INSERT INTO education (student_id, institution, degree, field_of_study, start_date, end_date, gpa, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$student['id'], $this->post('institution'), $this->post('degree'), $this->post('field_of_study'), $this->post('start_date') ?: null, $this->post('end_date') ?: null, $this->post('gpa') ?: null, $this->post('description')]);
        Flash::success('Education added.');
        $this->redirect('student/profile');
    }

    public function deleteEducation(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/profile'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/profile'); return; }
        $base = new \App\Models\BaseModel();
        $base->execute("DELETE FROM education WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        Flash::success('Education entry deleted.');
        $this->redirect('student/profile');
    }

    public function addExperience(): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/profile'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/profile'); return; }
        $base = new \App\Models\BaseModel();
        $isCurrent = !empty($_POST['is_current']) ? 1 : 0;
        $base->execute("INSERT INTO experience (student_id, company_name, position, start_date, end_date, is_current, description) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$student['id'], $this->post('company_name'), $this->post('position'), $this->post('start_date') ?: null, $this->post('end_date') ?: null, $isCurrent, $this->post('description')]);
        Flash::success('Experience added.');
        $this->redirect('student/profile');
    }

    public function deleteExperience(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/profile'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/profile'); return; }
        $base = new \App\Models\BaseModel();
        $base->execute("DELETE FROM experience WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        Flash::success('Experience entry deleted.');
        $this->redirect('student/profile');
    }

    public function deleteNotification(int $id): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $base = new \App\Models\BaseModel();
        $base->execute("DELETE FROM notifications WHERE id = ? AND user_id = ?", [$id, Session::userId()]);
        $this->json(['success' => true]);
    }

    public function clearAllNotifications(): void
    {
        if (!CSRF::check()) { $this->json(['success' => false, 'message' => 'Invalid request'], 403); return; }
        $base = new \App\Models\BaseModel();
        $base->execute("DELETE FROM notifications WHERE user_id = ?", [Session::userId()]);
        $this->json(['success' => true]);
    }
}