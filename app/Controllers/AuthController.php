<?php

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\Session;
use App\Helpers\CSRF;
use App\Helpers\Validator;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\EmployerModel;
use App\Models\NotificationModel;
use App\Models\AdminModel;

class AuthController extends BaseController
{
    private UserModel $userModel;
    private AdminModel $adminModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->adminModel = new AdminModel();
    }

    public function login(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        $this->view('auth/login');
    }

    public function handleLogin(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request. Please try again.');
            $this->redirect('login');
            return;
        }

        $email = $this->post('email');
        $password = $_POST['password'] ?? '';

        $validator = new Validator();
        $validator->validate(['email' => $email, 'password' => $password], [
            'email'    => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            Flash::error($validator->firstError('email') ?: $validator->firstError('password'));
            $this->redirect('login');
            return;
        }

        $user = $this->userModel->findWithRoleByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Flash::error('Invalid email or password.');
            $this->redirect('login');
            return;
        }

        if ($user['status'] !== 'active') {
            Flash::error('Your account is ' . $user['status'] . '. Please contact support.');
            $this->redirect('login');
            return;
        }

        // Set session
        Session::regenerate();
        Session::setUser($user);
        $this->userModel->updateLastLogin($user['id']);

        // Log activity
        $this->logActivity('login', $user['first_name'] . ' ' . $user['last_name'] . ' logged in');

        // Audit log for non-student logins
        if ($user['role_slug'] !== 'student') {
            $this->adminModel->logAudit($user['id'], 'login', 'users', $user['id']);
        }

        Flash::success('Welcome back, ' . $user['first_name'] . '!');
        $this->redirectToDashboard();
    }

    public function register(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        $this->view('auth/register');
    }

    public function handleRegister(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request. Please try again.');
            $this->redirect('register');
            return;
        }

        $data = [
            'role'         => $this->post('role'),
            'first_name'   => $this->post('first_name'),
            'last_name'    => $this->post('last_name'),
            'email'        => $this->post('email'),
            'password'     => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? ''
        ];

        $validator = new Validator();
        $validator->validate($data, [
            'role'         => 'required|in:student,employer,university,mentor',
            'first_name'   => 'required|min:2',
            'last_name'    => 'required|min:2',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Flash::error($validator->firstError('email') ?: $validator->firstError('password') ?: $validator->firstError('role') ?: 'Please fix the errors below.');
            $this->redirect('register');
            return;
        }

        // Get role ID
        $roles = $this->userModel->query("SELECT id, slug FROM roles WHERE slug = ?", [$data['role']]);
        if (empty($roles)) {
            Flash::error('Invalid role selected.');
            $this->redirect('register');
            return;
        }
        $roleId = $roles[0]['id'];

        // Create user
        $userId = $this->userModel->create([
            'role_id'    => $roleId,
            'email'      => $data['email'],
            'password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'status'     => 'active'
        ]);

        // Create role-specific profile
        switch ($data['role']) {
            case 'student':
                $studentModel = new StudentModel();
                $studentModel->create([
                    'user_id' => $userId,
                    'profile_completion' => 10
                ]);
                break;
            case 'employer':
                $employerModel = new EmployerModel();
                $employerModel->create([
                    'user_id' => $userId,
                    'company_name' => $data['first_name'] . "'s Company"
                ]);
                break;
            case 'university':
                $this->userModel->execute(
                    "INSERT INTO universities (user_id, uni_name) VALUES (?, ?)",
                    [$userId, $data['first_name'] . ' University']
                );
                break;
            case 'mentor':
                $this->userModel->execute(
                    "INSERT INTO mentors (user_id, availability) VALUES (?, 'available')",
                    [$userId]
                );
                break;
        }

        // Auto login
        $user = $this->userModel->findWithRole($userId);
        Session::regenerate();
        Session::setUser($user);
        $this->userModel->updateLastLogin($userId);

        $this->logActivity('registration', 'New ' . $data['role'] . ' registered: ' . $data['first_name'] . ' ' . $data['last_name']);

        // Notify admin
        $adminModel = new AdminModel();
        $notifModel = new NotificationModel();
        $admins = $this->userModel->query("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = 'admin'");
        foreach ($admins as $admin) {
            $notifModel->createNotification($admin['id'], 'system', 'New User Registration',
                $data['first_name'] . ' ' . $data['last_name'] . ' registered as ' . $data['role'] . '.');
        }

        Flash::success('Account created successfully! Welcome to ' . APP_NAME . '.');
        $this->redirectToDashboard();
    }

    public function logout(): void
    {
        $this->logActivity('logout', Session::userName() . ' logged out');
        Session::destroy();
        Flash::success('You have been logged out.');
        // Redirect to the homepage (not the login page) after logout
        $this->redirect('');
    }

    /**
     * AJAX: Save user's language preference to database + session.
     * Reads JSON body: { language: "en" }
     * Returns JSON: { success: true, language: "en" }
     */
    public function setLanguage(): void
    {
        // H-4 fix: Enforce CSRF protection on language-change endpoint.
        // The client already sends X-CSRF-Token header; the server must validate it.
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid or missing CSRF token'], 403);
            return;
        }

        // Parse input from JSON body or POST
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $lang = $input['language'] ?? $_POST['language'] ?? 'en';
        $allowed = ['en', 'fr', 'rw', 'sw', 'ar'];
        if (!in_array($lang, $allowed)) {
            $this->json(['success' => false, 'message' => 'Invalid language'], 400);
            return;
        }

        // Save to session (works for both logged-in and guest)
        Session::set('userLanguage', $lang);

        // Save to database if logged in
        if (Session::isLoggedIn()) {
            $userId = Session::userId();
            try {
                $db = \App\Config\Database::getInstance()->getConnection();
                $db->prepare("UPDATE users SET language = ? WHERE id = ?")->execute([$lang, $userId]);
            } catch (\Throwable $e) {
                // Column might not exist — fall back to cookie+session only
            }
        }

        // Always set cookie (works for guest + logged-in)
        setcookie('ss_lang', $lang, time() + 31536000, '/');

        $this->json(['success' => true, 'language' => $lang]);
    }

    /**
     * Universal account info update — works for ALL logged-in roles.
     * Lets users edit their first name, last name, email, phone, and avatar
     * after registration, from the Settings page.
     *
     * Route: POST /account/update
     * Fields: first_name, last_name, email, phone, avatar (file upload)
     */
    public function updateAccount(): void
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('login');
            return;
        }
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('');
            return;
        }

        $userId = Session::userId();
        $userModel = new UserModel();

        // Validate required fields
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');

        if ($firstName === '' || $lastName === '') {
            Flash::error('First name and last name are required.');
            $this->redirectBack();
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please enter a valid email address.');
            $this->redirectBack();
            return;
        }

        // Check email uniqueness (exclude the current user)
        $existing = $userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== (int)$userId) {
            Flash::error('That email is already in use by another account.');
            $this->redirectBack();
            return;
        }

        // Build update data
        $updateData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone ?: null,
        ];

        // Handle avatar upload (optional) — uses the same Upload::handle() pattern
        // as the student profile controller, storing the path as 'uploads/avatars/file_xxx.jpg'
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload = \App\Helpers\Upload::handle($_FILES['avatar'], 'uploads/avatars', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                $updateData['avatar'] = $upload['path'];
            } else {
                Flash::error('Avatar upload failed: ' . $upload['message']);
                $this->redirectBack();
                return;
            }
        }

        // Update the users table
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            $setParts = [];
            $params = [];
            foreach ($updateData as $col => $val) {
                $setParts[] = "`{$col}` = ?";
                $params[] = $val;
            }
            $params[] = $userId;
            $stmt = $db->prepare("UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?");
            $stmt->execute($params);
        } catch (\Throwable $e) {
            Flash::error('Failed to update account: ' . $e->getMessage());
            $this->redirectBack();
            return;
        }

        // Update session so the new name/avatar show immediately
        Session::set('userName', $firstName . ' ' . $lastName);
        Session::set('userEmail', $email);
        if (!empty($updateData['avatar'])) {
            Session::set('userAvatar', $updateData['avatar']);
        }

        // Log the activity
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            $db->prepare("INSERT INTO activity_logs (user_id, type, description) VALUES (?, ?, ?)")
               ->execute([$userId, 'profile_update', 'Updated account information']);
        } catch (\Throwable $e) {}

        Flash::success('Account information updated successfully.');
        $this->redirectBack();
    }

    /**
     * Redirect back to the previous page (or dashboard if no referer).
     */
    private function redirectBack(): void
    {
        $role = Session::userRole() ?? 'student';
        $dashboardMap = [
            'student'    => 'student/settings',
            'employer'   => 'employer/settings',
            'university' => 'university/dashboard',
            'mentor'     => 'mentor/dashboard',
            'admin'      => 'admin/settings',
        ];
        $fallback = $dashboardMap[$role] ?? 'student/settings';
        $this->redirect($fallback);
    }

    public function forgotPassword(): void
    {
        $this->view('auth/forgot-password');
    }

    public function handleForgotPassword(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('forgot-password');
            return;
        }

        $email = $this->post('email');
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->execute(
                "INSERT INTO password_resets (email, token) VALUES (?, ?)",
                [$email, $token]
            );
            // In production, send email with reset link
            // For demo, just show success
        }

        Flash::success('If an account exists with that email, a reset link has been sent.');
        $this->redirect('login');
    }

    public function resetPassword(string $token): void
    {
        // Show the reset form — verify token exists in password_resets table
        $base = new \App\Models\BaseModel();
        $reset = $base->queryOne("SELECT * FROM password_resets WHERE token = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)", [$token]);
        if (!$reset) {
            Flash::error('Invalid or expired reset token.');
            $this->redirect('forgot-password');
            return;
        }
        $this->view('auth/reset-password', ['token' => $token, 'email' => $reset['email']]);
    }

    public function handleResetPassword(): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('forgot-password'); return; }
        $token = $this->post('token');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';
        if (strlen($password) < 8) { Flash::error('Password must be at least 8 characters.'); $this->redirect('reset-password/' . $token); return; }
        if ($password !== $confirm) { Flash::error('Passwords do not match.'); $this->redirect('reset-password/' . $token); return; }
        $base = new \App\Models\BaseModel();
        $reset = $base->queryOne("SELECT * FROM password_resets WHERE token = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)", [$token]);
        if (!$reset) { Flash::error('Invalid or expired reset token.'); $this->redirect('forgot-password'); return; }
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $base->execute("UPDATE users SET password = ? WHERE email = ?", [$hashed, $reset['email']]);
        $base->execute("DELETE FROM password_resets WHERE token = ?", [$token]);
        Flash::success('Password reset successfully. Please log in.');
        $this->redirect('login');
    }

    private function redirectToDashboard(): void
    {
        $role = Session::userRole();
        $routes = [
            'admin'     => 'admin/dashboard',
            'student'   => 'student/dashboard',
            'employer'  => 'employer/dashboard',
            'university'=> 'university/dashboard',
            'mentor'    => 'mentor/dashboard'
        ];
        $this->redirect($routes[$role] ?? '/');
    }
}