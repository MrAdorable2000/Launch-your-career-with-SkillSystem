<?php

namespace App\Controllers;

use App\Helpers\URL;
use App\Helpers\Flash;
use App\Helpers\Session;
use App\Helpers\CSRF;
use App\Models\NotificationModel;
use App\Models\MessageModel;

abstract class BaseController
{
    protected string $currentView = '';

    protected function view(string $view, array $data = []): void
    {
        // Store current view name for layout selection
        $this->currentView = $view;

        // Extract data to local scope for views
        extract($data);

        // Shared data available in all views
        $isLoggedIn = Session::isLoggedIn();
        $userName = Session::userName();
        $userRole = Session::userRole();
        $userId = Session::userId();
        $userAvatar = Session::get('userAvatar');
        $flashMessage = Flash::has() ? Flash::display() : '';
        $csrfField = CSRF::field();
        $appUrl = APP_URL;
        $appName = APP_NAME;

        // Get notification and message counts for logged-in users
        $unreadNotifications = 0;
        $unreadMessages = 0;
        $recentNotifications = [];

        if ($isLoggedIn) {
            $notifModel = new NotificationModel();
            $msgModel = new MessageModel();
            $unreadNotifications = $notifModel->getUnreadCount($userId);
            $unreadMessages = $msgModel->getUnreadCount($userId);
            $recentNotifications = $notifModel->getRecentForUser($userId, 5);
        }

        // Determine which layout to use
        $layoutPath = $this->getLayoutPath();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout file not found: {$layoutPath}");
        }

        // Capture view content with error handling
        try {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new \RuntimeException("Error in view '{$view}': " . $e->getMessage() . " (File: " . $e->getFile() . ":" . $e->getLine() . ")", 0, $e);
        }

        // Include the layout (which uses $content)
        try {
            include $layoutPath;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error in layout: " . $e->getMessage() . " (File: " . $e->getFile() . ":" . $e->getLine() . ")", 0, $e);
        }
    }

    protected function getLayoutPath(): string
    {
        // Auth pages use the auth layout (login, register, forgot-password)
        $currentView = $this->currentView ?? '';
        if (strpos($currentView, 'auth/') === 0) {
            return VIEWS_PATH . '/layouts/auth.php';
        }
        // Verify pages use landing layout
        if (strpos($currentView, 'verify/') === 0) {
            return VIEWS_PATH . '/layouts/landing.php';
        }
        // Logged-in users use the app layout
        if (Session::isLoggedIn()) {
            return VIEWS_PATH . '/layouts/app.php';
        }
        // Default: landing layout (homepage, 404, etc.)
        return VIEWS_PATH . '/layouts/landing.php';
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $path): void
    {
        URL::redirect($path);
    }

    protected function sanitize(string $str): string
    {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }

    protected function post(string $key, string $default = ''): string
    {
        return $this->sanitize($_POST[$key] ?? $default);
    }

    protected function getInt(string $key, int $default = 0): int
    {
        return (int) ($_GET[$key] ?? $_POST[$key] ?? $default);
    }

    protected function logActivity(string $type, string $description): void
    {
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            $db->prepare("INSERT INTO activity_logs (user_id, type, description) VALUES (?, ?, ?)")
               ->execute([Session::userId() ?? null, $type, $description]);
        } catch (\Throwable $e) {
            // Silent fail for activity logging
        }
    }
}