<?php
/**
 * SkillSystem - Front Controller
 * All requests are routed through this file
 *
 * This version is designed to NEVER show a blank white page.
 * It outputs HTML immediately and checks every file before loading.
 */

// ---- CRITICAL: Error reporting (H-1 hardening) ----
// H-1 fix: In production, errors must NEVER be shown to the browser.
// We still log everything, but display_errors follows APP_DEBUG (defaults to false).
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', '0');      // Default OFF — overridden below after .env loads
ini_set('display_startup_errors', '0');

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Helper to show a fatal error page and stop
function ss_fatal($title, $message, $detail = '') {
    while (ob_get_level() > 0) { @ob_end_clean(); }
    http_response_code(500);
    $t = htmlspecialchars($title);
    $m = htmlspecialchars($message);
    $d = htmlspecialchars($detail);
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>SkillSystem Error</title>";
    echo "<link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap' rel='stylesheet'>";
    echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' rel='stylesheet'>";
    echo "<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#0f172a;color:#f1f5f9;padding:24px;min-height:100vh;display:flex;align-items:center;justify-content:center}.box{max-width:700px;background:#1e293b;border:1px solid #334155;border-radius:16px;padding:32px;width:100%}.icon{width:56px;height:56px;border-radius:14px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:24px;color:#ef4444}h1{font-size:20px;font-weight:700;margin-bottom:8px}.msg{background:#0f172a;border:1px solid #ef4444;border-radius:10px;padding:14px 18px;font-size:14px;color:#fca5a5;margin-bottom:16px;font-family:monospace;word-break:break-word}.det{font-size:12px;color:#94a3b8;font-family:monospace;background:#0f172a;padding:12px;border-radius:8px;border:1px solid #334155;white-space:pre-wrap;word-break:break-all}.act{margin-top:20px;display:flex;gap:12px;flex-wrap:wrap}a.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px}a.btn-primary{background:#059669;color:#fff}a.btn-secondary{background:#334155;color:#f1f5f9}</style></head><body>";
    echo "<div class='box'><div class='icon'><i class='fas fa-exclamation-triangle'></i></div>";
    echo "<h1>{$t}</h1><div class='msg'>{$m}</div>";
    if ($d) echo "<div class='det'>{$d}</div>";
    echo "<div class='act'><a href='/' class='btn btn-primary'><i class='fas fa-home'></i> Home</a>";
    echo "<a href='/diagnostic.php' class='btn btn-secondary'><i class='fas fa-wrench'></i> Diagnostic</a></div>";
    echo "</div></body></html>";
    exit;
}

// Register shutdown handler for fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ss_fatal(
            'PHP Fatal Error',
            $error['message'],
            "File: " . $error['file'] . "\nLine: " . $error['line']
        );
    }
});

// ---- Step 1: Parse .env file ----
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines) {
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
    }
}

// ---- Step 2: Define application constants ----
define('APP_ENV', $env['APP_ENV'] ?? 'local');
// H-1 fix: Default APP_DEBUG to false (production-safe). Set APP_DEBUG=true in .env for local dev.
define('APP_DEBUG', ($env['APP_DEBUG'] ?? 'false') === 'true');
// Only show errors in the browser when APP_DEBUG is explicitly true
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}
define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('PUBLIC_PATH', __DIR__ . '/public');
define('STORAGE_PATH', __DIR__ . '/storage');
define('LOGS_PATH', __DIR__ . '/logs');
define('VIEWS_PATH', APP_PATH . '/Views');
define('LANGUAGES_PATH', APP_PATH . '/Languages');
define('APP_NAME', $env['APP_NAME'] ?? 'SkillSystem');
// Auto-detect APP_URL from server variables if .env doesn't match actual folder
$envAppUrl = $env['APP_URL'] ?? 'http://localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
if ($scriptDir === '/' || $scriptDir === '.') $scriptDir = '';
$detectedUrl = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $scriptDir;
// Use detected URL if the .env URL's folder doesn't match the actual folder
$envFolder = rtrim(parse_url($envAppUrl, PHP_URL_PATH) ?? '', '/');
if ($envFolder !== $scriptDir && $scriptDir !== '') {
    define('APP_URL', $detectedUrl);
} else {
    define('APP_URL', $envAppUrl);
}
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_PORT', $env['DB_PORT'] ?? '3306');
define('DB_NAME', $env['DB_NAME'] ?? 'skillsystem');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('SESSION_LIFETIME', (int)($env['SESSION_LIFETIME'] ?? 7200));

// ---- Step 3: Verify critical files exist and are readable ----
$criticalFiles = [
    'app/Helpers/Session.php',
    'app/Helpers/CSRF.php',
    'app/Helpers/Flash.php',
    'app/Helpers/URL.php',
    'app/Helpers/Validator.php',
    'app/Helpers/Upload.php',
    'app/Helpers/functions.php',
    'app/Helpers/I18n.php',
    'app/Config/Database.php',
    'app/Libraries/Router.php',
    'routes/web.php',
];

foreach ($criticalFiles as $relPath) {
    $fullPath = __DIR__ . '/' . $relPath;
    if (!file_exists($fullPath)) {
        ss_fatal('Missing File', "Required file not found: {$relPath}", "Full path: {$fullPath}\n\nPlease re-download and extract the complete SkillSystem package.");
    }
}

// ---- Step 4: PSR-4 Autoloader ----
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// ---- Step 5: Start session (M-6 hardening) ----
// M-6 fix: Set secure session cookie parameters BEFORE session_start().
// - httponly: prevents JavaScript from reading the session cookie (XSS mitigation)
// - samesite=Strict: prevents CSRF via cross-site requests
// - secure: only send over HTTPS (auto-detected from request scheme)
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $isHttps,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ---- Step 6: Load helper files (with error catching) ----
try {
    require_once APP_PATH . '/Helpers/Session.php';
    require_once APP_PATH . '/Helpers/CSRF.php';
    require_once APP_PATH . '/Helpers/Flash.php';
    require_once APP_PATH . '/Helpers/URL.php';
    require_once APP_PATH . '/Helpers/Validator.php';
    require_once APP_PATH . '/Helpers/Upload.php';
    require_once APP_PATH . '/Helpers/functions.php';
    require_once APP_PATH . '/Helpers/I18n.php';
} catch (\Throwable $e) {
    ss_fatal('Helper Loading Error', $e->getMessage(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
}

// ---- Step 7: Import namespaces and create aliases ----
use App\Helpers\Session;
use App\Helpers\CSRF;
use App\Helpers\Flash;
use App\Helpers\URL as HelperURL;
use App\Helpers\Validator;
use App\Helpers\Upload;
use App\Helpers\I18n;

if (!class_exists('CSRF', false)) class_alias(CSRF::class, 'CSRF');
if (!class_exists('Flash', false)) class_alias(Flash::class, 'Flash');
if (!class_exists('URL', false)) class_alias(HelperURL::class, 'URL');
if (!class_exists('Validator', false)) class_alias(Validator::class, 'Validator');
if (!class_exists('Upload', false)) class_alias(Upload::class, 'Upload');
if (!class_exists('Session', false)) class_alias(Session::class, 'Session');

// ---- Step 8: Initialize CSRF ----
try {
    CSRF::init();
} catch (\Throwable $e) {
    ss_fatal('CSRF Init Error', $e->getMessage(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
}

// ---- Step 9: Get the requested URL ----
// Auto-detect base folder from SCRIPT_NAME (works with any folder name)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';  // e.g. /skillsystem/index.php
$baseDir = str_replace('\\', '/', dirname($scriptName)); // e.g. /skillsystem
if ($baseDir === '/' || $baseDir === '.') $baseDir = '';

$requestUrl = '';

// Method 1: ?url= parameter (from .htaccess RewriteRule)
if (isset($_GET['url']) && $_GET['url'] !== '') {
    $requestUrl = $_GET['url'];
}

// Method 2: PATH_INFO
if (empty($requestUrl) && isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
    $requestUrl = $_SERVER['PATH_INFO'];
}

// Method 3: Parse REQUEST_URI and strip the base folder
if (empty($requestUrl)) {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Remove query string
    if (($pos = strpos($uri, '?')) !== false) {
        $uri = substr($uri, 0, $pos);
    }
    // Strip base directory prefix (e.g. /skillsystem/mentor/sessions -> /mentor/sessions)
    if (!empty($baseDir) && strpos($uri, $baseDir) === 0) {
        $uri = substr($uri, strlen($baseDir));
    }
    $requestUrl = $uri;
}

// Normalize: ensure leading slash, no trailing slash
$requestUrl = '/' . ltrim($requestUrl, '/');
$requestUrl = rtrim($requestUrl, '/');
if ($requestUrl === '') $requestUrl = '/';

// Debug: log the parsed URL (helps diagnose routing issues)
@file_put_contents(__DIR__ . '/logs/route_debug.log', date('Y-m-d H:i:s') . " REQUEST_URI={$_SERVER['REQUEST_URI']} SCRIPT_NAME={$_SERVER['SCRIPT_NAME']} parsed={$requestUrl} GET_url=" . ($_GET['url'] ?? 'NONE') . "\n", FILE_APPEND);

// ---- Step 10: Load routes ----
$routesFile = __DIR__ . '/routes/web.php';
try {
    $routes = require $routesFile;
} catch (\Throwable $e) {
    ss_fatal('Routes Loading Error', $e->getMessage(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
}

if (!is_array($routes)) {
    ss_fatal('Routes Error', 'routes/web.php must return an array', 'Got: ' . gettype($routes));
}

// ---- Step 11: Dispatch the request ----
// NOTE: The runtime TranslationFilter (ob_start + TranslationFilter::apply) was
// removed because it corrupted the HTML structure — translating inside CSS
// classes, inline styles, and data attributes, which broke the page layout.
// Translation now happens only via explicit __() calls in the layout files
// (app.php, landing.php, auth.php) and view files that opt in. This is the
// safe, working approach — HTML structure is never touched.
try {
    $router = new App\Libraries\Router($routes);
    $router->dispatch($requestUrl);
} catch (\Throwable $e) {
    ss_fatal(
        'Application Error',
        $e->getMessage(),
        "Type: " . get_class($e) . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine() . "\n\nStack Trace:\n" . $e->getTraceAsString()
    );
}
