<?php

namespace App\Libraries;

use App\Helpers\URL;

class Router
{
    /** @var array<string, mixed> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $params = [];

    /** @var string */
    private string $currentRoute = '';

    /**
     * @param mixed $routes Array of route definitions
     */
    public function __construct($routes)
    {
        if (!is_array($routes)) {
            $routes = [];
        }
        $this->routes = $routes;
    }

    /**
     * Match and dispatch a URL to its controller action.
     * Tries the URL as-is, then tries stripping the first segment (base folder).
     */
    public function dispatch(string $url): void
    {
        // Normalize: decode, trim, ensure leading slash
        $url = '/' . trim(rawurldecode($url), '/');
        $method = $_SERVER['REQUEST_METHOD'];

        // Try matching the URL as-is
        if ($this->tryRoutes($url, $method)) {
            return;
        }

        // If no match and URL has more than one segment, try stripping the first
        // segment (it might be the base folder name that wasn't stripped by index.php)
        $parts = explode('/', trim($url, '/'));
        if (count($parts) > 1) {
            array_shift($parts);
            $altUrl = '/' . implode('/', $parts);
            if ($this->tryRoutes($altUrl, $method)) {
                return;
            }
            // Try stripping TWO segments (edge case: subfolder/subfolder/path)
            if (count($parts) > 1) {
                array_shift($parts);
                $altUrl2 = '/' . implode('/', $parts);
                if ($this->tryRoutes($altUrl2, $method)) {
                    return;
                }
            }
        }

        // 404 - No route matched
        http_response_code(404);
        @file_put_contents(ROOT_PATH . '/logs/404_debug.log',
            date('Y-m-d H:i:s') . " 404 URL: {$url} method: {$method}\n", FILE_APPEND);
        $this->callAction('HomeController@notFound');
    }

    /**
     * Try all routes against a URL. Returns true if matched.
     */
    private function tryRoutes(string $url, string $method): bool
    {
        foreach ($this->routes as $pattern => $handler) {
            if (!is_string($handler) && !is_array($handler)) {
                continue;
            }

            $routeMethod = is_array($handler) ? ($handler['method'] ?? 'GET') : 'GET';
            $routeTarget = is_array($handler) ? ($handler['handler'] ?? '') : $handler;

            if (strtoupper($routeMethod) !== $method) {
                continue;
            }

            if (empty($routeTarget) || !is_string($routeTarget)) {
                continue;
            }

            // Convert pattern like /student/jobs/{id} to regex
            $regex = '~^' . preg_replace('~\{([a-zA-Z_]+)\}~', '(?P<\1>[^/]+)', $pattern) . '$~';

            if (preg_match($regex, $url, $matches)) {
                $this->currentRoute = $pattern;
                $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callAction($routeTarget);
                return true;
            }
        }
        return false;
    }

    /**
     * Instantiate controller and call the action method.
     */
    private function callAction(string $target): void
    {
        $parts = explode('@', $target, 2);
        if (count($parts) !== 2) {
            $this->handleError("Invalid route target: {$target}. Expected 'Controller@action' format.");
            return;
        }

        [$controller, $action] = $parts;
        $fullController = 'App\\Controllers\\' . $controller;

        if (!class_exists($fullController)) {
            $this->handleError("Controller class not found: {$fullController}");
            return;
        }

        $instance = new $fullController();

        if (!method_exists($instance, $action)) {
            $this->handleError("Method {$action} does not exist on {$fullController}");
            return;
        }

        call_user_func_array([$instance, $action], array_values($this->params));
    }

    /**
     * Display a user-friendly error instead of a fatal one
     */
    private function handleError(string $message): void
    {
        if (APP_DEBUG) {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            echo '<title>SkillSystem Error</title>';
            echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">';
            echo '<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#0f172a;color:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}';
            echo '.error-box{max-width:560px;width:100%;background:#1e293b;border:1px solid #334155;border-radius:20px;padding:40px;text-align:center}';
            echo '.error-icon{width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:28px;color:#ef4444}';
            echo 'h2{font-size:22px;font-weight:700;margin-bottom:12px;color:#f1f5f9}';
            echo 'p{font-size:14px;color:#94a3b8;line-height:1.7;margin-bottom:24px}';
            echo 'code{display:block;background:#0f172a;border:1px solid #334155;border-radius:10px;padding:14px;font-size:13px;color:#f87171;text-align:left;word-break:break-all;margin-bottom:20px;font-family:monospace}';
            echo 'a{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#2563eb;color:#fff;border-radius:12px;text-decoration:none;font-weight:600;font-size:14px;transition:all .2s}';
            echo 'a:hover{background:#1d4ed8;transform:translateY(-2px)}</style></head><body>';
            echo '<div class="error-box">';
            echo '<div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>';
            echo '<h2>Something Went Wrong</h2>';
            echo '<p>An error occurred while processing your request.</p>';
            echo '<code>' . htmlspecialchars($message) . '</code>';
            echo '<a href="' . APP_URL . '"><i class="fas fa-home"></i> Go to Homepage</a>';
            echo '</div></body></html>';
        } else {
            http_response_code(500);
            echo 'An internal error occurred. Please try again later.';
        }
        exit;
    }

    /**
     * Get the currently matched route pattern
     */
    public function currentRoute(): string
    {
        return $this->currentRoute;
    }
}
