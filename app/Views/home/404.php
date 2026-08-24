<?php
/**
 * 404 Page — Premium Enterprise Error Page
 *
 * Rendered inside layouts/app.php (authenticated users) or layouts/landing.php (guests).
 * Variables available from BaseController:
 *   $isLoggedIn, $userName, $userRole, $userId, $csrfField, $appUrl, $appName
 */
use App\Helpers\URL;
use App\Helpers\Session;

$pageTitle = 'Page Not Found';

// Gather smart context
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$timestamp = date('Y-m-d H:i:s');
$sessionId = session_id();
$userRole = Session::userRole() ?? 'guest';
$isAdmin = Session::isAdmin();

// Role-based dashboard URL
$dashboardUrl = '#';
if ($userRole === 'admin') $dashboardUrl = URL::to('admin/dashboard');
elseif ($userRole === 'student') $dashboardUrl = URL::to('student/dashboard');
elseif ($userRole === 'employer') $dashboardUrl = URL::to('employer/dashboard');
elseif ($userRole === 'university') $dashboardUrl = URL::to('university/dashboard');
elseif ($userRole === 'mentor') $dashboardUrl = URL::to('mentor/dashboard');
elseif (!$isLoggedIn) $dashboardUrl = URL::to('login');

// Role-based quick links
$quickLinks = [];
if ($isLoggedIn) {
    if ($userRole === 'student') {
        $quickLinks = [
            ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'url' => URL::to('student/dashboard')],
            ['icon' => 'fa-user-circle', 'label' => 'Profile', 'url' => URL::to('student/profile')],
            ['icon' => 'fa-briefcase', 'label' => 'Jobs', 'url' => URL::to('student/jobs')],
            ['icon' => 'fa-user-graduate', 'label' => 'Internships', 'url' => URL::to('student/jobs')],
            ['icon' => 'fa-certificate', 'label' => 'Certificates', 'url' => URL::to('student/certificates')],
            ['icon' => 'fa-folder-plus', 'label' => 'Portfolio', 'url' => URL::to('student/portfolio')],
        ];
    } elseif ($userRole === 'employer') {
        $quickLinks = [
            ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'url' => URL::to('employer/dashboard')],
            ['icon' => 'fa-plus-circle', 'label' => 'Post Job', 'url' => URL::to('employer/post-job')],
            ['icon' => 'fa-folder-open', 'label' => 'Applications', 'url' => URL::to('employer/jobs')],
            ['icon' => 'fa-user-graduate', 'label' => 'Internships', 'url' => URL::to('employer/internships')],
            ['icon' => 'fa-building', 'label' => 'Company', 'url' => URL::to('employer/company')],
            ['icon' => 'fa-cog', 'label' => 'Settings', 'url' => URL::to('employer/settings')],
        ];
    } elseif ($userRole === 'university') {
        $quickLinks = [
            ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'url' => URL::to('university/dashboard')],
            ['icon' => 'fa-users', 'label' => 'Students', 'url' => URL::to('university/students')],
            ['icon' => 'fa-chart-bar', 'label' => 'Reports', 'url' => URL::to('university/reports')],
            ['icon' => 'fa-university', 'label' => 'Profile', 'url' => URL::to('university/dashboard')],
        ];
    } elseif ($userRole === 'mentor') {
        $quickLinks = [
            ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'url' => URL::to('mentor/dashboard')],
            ['icon' => 'fa-calendar-check', 'label' => 'Sessions', 'url' => URL::to('mentor/sessions')],
            ['icon' => 'fa-envelope', 'label' => 'Messages', 'url' => URL::to('mentor/messages')],
        ];
    } elseif ($userRole === 'admin') {
        $quickLinks = [
            ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'url' => URL::to('admin/dashboard')],
            ['icon' => 'fa-users', 'label' => 'Users', 'url' => URL::to('admin/users')],
            ['icon' => 'fa-chart-bar', 'label' => 'Analytics', 'url' => URL::to('admin/analytics')],
            ['icon' => 'fa-file-alt', 'label' => 'Reports', 'url' => URL::to('admin/reports')],
            ['icon' => 'fa-cog', 'label' => 'Settings', 'url' => URL::to('admin/settings')],
            ['icon' => 'fa-shield-alt', 'label' => 'Security', 'url' => URL::to('admin/security')],
        ];
    }
} else {
    $quickLinks = [
        ['icon' => 'fa-home', 'label' => 'Homepage', 'url' => URL::to('/')],
        ['icon' => 'fa-sign-in-alt', 'label' => 'Sign In', 'url' => URL::to('login')],
        ['icon' => 'fa-user-plus', 'label' => 'Register', 'url' => URL::to('register')],
        ['icon' => 'fa-briefcase', 'label' => 'Jobs', 'url' => URL::to('/#jobs')],
    ];
}

// Possible reasons
$reasons = [
    'The URL may have been typed incorrectly.',
    'The page may have been moved or renamed.',
    'You may not have permission to access this resource.',
    'The resource may have been deleted.',
];
?>

<!-- ==================== HERO 404 ==================== -->
<div class="ss-card ss-card-glass ss-animate-fade-up" style="max-width:900px;margin:0 auto;padding:3rem 2rem;text-align:center;overflow:hidden;position:relative;">
    <!-- Decorative background -->
    <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:var(--ss-grad-primary);opacity:0.05;pointer-events:none;"></div>
    <div style="position:absolute;bottom:-30px;left:-30px;width:150px;height:150px;border-radius:50%;background:var(--ss-accent);opacity:0.05;pointer-events:none;"></div>

    <!-- Animated SVG Illustration -->
    <div style="position:relative;z-index:1;margin-bottom:1.5rem;">
        <svg width="120" height="120" viewBox="0 0 120 120" fill="none" style="margin:0 auto;display:block;" aria-hidden="true">
            <circle cx="60" cy="60" r="50" stroke="var(--ss-primary)" stroke-width="3" stroke-dasharray="8 6" opacity="0.3">
                <animateTransform attributeName="transform" type="rotate" from="0 60 60" to="360 60 60" dur="20s" repeatCount="indefinite"/>
            </circle>
            <circle cx="60" cy="60" r="35" fill="var(--ss-primary-light)" opacity="0.5"/>
            <text x="60" y="72" text-anchor="middle" font-size="28" font-weight="900" fill="var(--ss-primary)" font-family="Poppins, sans-serif">404</text>
            <circle cx="95" cy="35" r="8" fill="var(--ss-warning)">
                <animate attributeName="cy" values="35;30;35" dur="2s" repeatCount="indefinite"/>
            </circle>
            <circle cx="25" cy="85" r="5" fill="var(--ss-accent)">
                <animate attributeName="cy" values="85;80;85" dur="1.5s" repeatCount="indefinite"/>
            </circle>
        </svg>
    </div>

    <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;letter-spacing:-0.03em;margin-bottom:0.5rem;">
        Page Not Found
    </h1>
    <p style="color:var(--ss-text-2);font-size:1.05rem;max-width:480px;margin:0 auto 2rem;line-height:1.6;">
        The page you are looking for may have been moved, deleted, or you may not have permission to access it.
    </p>

    <!-- Smart Action Buttons -->
    <div class="d-flex gap-2 justify-content-center flex-wrap mb-4">
        <?php if ($isLoggedIn): ?>
        <a href="<?= $dashboardUrl ?>" class="ss-btn ss-btn-gradient"><i class="fas fa-tachometer-alt"></i> Go to Dashboard</a>
        <?php else: ?>
        <a href="<?= URL::to('/') ?>" class="ss-btn ss-btn-gradient"><i class="fas fa-home"></i> Homepage</a>
        <a href="<?= URL::to('login') ?>" class="ss-btn ss-btn-gradient"><i class="fas fa-sign-in-alt"></i> Sign In</a>
        <?php endif; ?>
        <a href="javascript:history.back()" class="ss-btn ss-btn-light"><i class="fas fa-arrow-left"></i> Go Back</a>
        <a href="javascript:location.reload()" class="ss-btn ss-btn-light"><i class="fas fa-redo"></i> Refresh</a>
    </div>
    <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="#searchSection" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-search"></i> Search System</a>
        <a href="<?= URL::to('/') ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-headset"></i> Contact Support</a>
        <a href="mailto:admin@skillsystem.rw?subject=404 Error Report&body=URL: <?= urlencode($currentUrl) ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-bug"></i> Report Error</a>
    </div>
</div>

<!-- ==================== SMART INFO + SEARCH ==================== -->
<div class="row g-4 mt-1">
    <!-- Search Section -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up ss-delay-1" id="searchSection">
            <div class="ss-card-header">
                <h3><i class="fas fa-search text-primary"></i> Search the System</h3>
            </div>
            <div class="ss-card-body">
                <div class="ss-input-icon mb-3">
                    <i class="fas fa-search"></i>
                    <input type="text" class="ss-input" id="errSearchInput" placeholder="Search jobs, students, companies..." oninput="filterQuickLinks(this.value)">
                </div>
                <div style="font-size:0.72rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Quick Search</div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= URL::to('student/jobs') ?>" class="ss-chip"><i class="fas fa-briefcase"></i> Jobs</a>
                    <a href="<?= URL::to('student/jobs') ?>" class="ss-chip"><i class="fas fa-user-graduate"></i> Internships</a>
                    <a href="<?= URL::to('student/forum') ?>" class="ss-chip"><i class="fas fa-comments"></i> Discussions</a>
                    <a href="<?= URL::to('student/mentors') ?>" class="ss-chip"><i class="fas fa-chalkboard-teacher"></i> Mentors</a>
                    <a href="<?= URL::to('student/certificates') ?>" class="ss-chip"><i class="fas fa-certificate"></i> Certificates</a>
                    <a href="<?= URL::to('student/events') ?>" class="ss-chip"><i class="fas fa-calendar-alt"></i> Events</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Info -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up ss-delay-2">
            <div class="ss-card-header">
                <h3><i class="fas fa-info-circle text-primary"></i> What Happened?</h3>
            </div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;color:var(--ss-text-3);">Requested URL</span>
                        <span style="font-size:0.82rem;font-weight:600;font-family:var(--ss-font-mono);word-break:break-all;text-align:right;"><?= htmlspecialchars($currentUrl) ?></span>
                    </div>
                    <div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;color:var(--ss-text-3);">Your Role</span>
                        <span style="font-size:0.82rem;font-weight:600;text-transform:capitalize;"><?= htmlspecialchars($userRole) ?></span>
                    </div>
                    <div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;color:var(--ss-text-3);">Request Method</span>
                        <span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($requestMethod) ?></span>
                    </div>
                    <div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;color:var(--ss-text-3);">Timestamp</span>
                        <span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($timestamp) ?></span>
                    </div>
                </div>
                <div style="font-size:0.72rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin:1rem 0 0.5rem;">Possible Reasons</div>
                <div class="d-flex flex-column gap-1">
                    <?php foreach ($reasons as $r): ?>
                    <div style="font-size:0.8rem;color:var(--ss-text-2);display:flex;align-items:flex-start;gap:6px;">
                        <i class="fas fa-circle" style="font-size:0.4rem;color:var(--ss-primary);margin-top:6px;flex-shrink:0;"></i>
                        <?= htmlspecialchars($r) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== QUICK LINKS ==================== -->
<?php if (!empty($quickLinks)): ?>
<div class="ss-card mt-4 ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-bolt text-primary"></i> Quick Links</h3>
        <span class="ss-badge ss-badge-soft text-capitalize"><?= htmlspecialchars($userRole) ?> section</span>
    </div>
    <div class="ss-card-body">
        <div class="row g-3" id="quickLinksGrid">
            <?php foreach ($quickLinks as $link): ?>
            <div class="col-6 col-md-4 col-lg-2 quick-link-item" data-label="<?= htmlspecialchars(strtolower($link['label'])) ?>">
                <a href="<?= $link['url'] ?>" class="text-decoration-none">
                    <div class="d-flex flex-column align-items-center gap-2 p-3 ss-card-hover" style="border:1px solid var(--ss-border);border-radius:var(--ss-r);transition:all 0.2s ease;">
                        <div style="width:44px;height:44px;border-radius:var(--ss-r-sm);background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;">
                            <i class="fas <?= $link['icon'] ?>"></i>
                        </div>
                        <div style="font-size:0.78rem;font-weight:600;color:var(--ss-text);"><?= htmlspecialchars($link['label']) ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ==================== HELP SECTION ==================== -->
<div class="ss-card mt-4 ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-question-circle text-primary"></i> Need Help?</h3>
    </div>
    <div class="ss-card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="d-flex flex-column align-items-center text-center p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r);">
                    <div style="width:40px;height:40px;border-radius:50%;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.5rem;"><i class="fas fa-link"></i></div>
                    <div style="font-size:0.82rem;font-weight:600;margin-bottom:2px;">Check the URL</div>
                    <div style="font-size:0.72rem;color:var(--ss-text-3);">Make sure it's spelled correctly</div>
                </div>
            </div>
            <div class="col-md-3">
                <a href="<?= $dashboardUrl ?>" class="text-decoration-none">
                    <div class="d-flex flex-column align-items-center text-center p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r);">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.5rem;"><i class="fas fa-tachometer-alt"></i></div>
                        <div style="font-size:0.82rem;font-weight:600;margin-bottom:2px;">Go to Dashboard</div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">Return to your home page</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="#searchSection" class="text-decoration-none">
                    <div class="d-flex flex-column align-items-center text-center p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r);">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--ss-info-light);color:var(--ss-info);display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.5rem;"><i class="fas fa-search"></i></div>
                        <div style="font-size:0.82rem;font-weight:600;margin-bottom:2px;">Search Again</div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">Find what you need</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="mailto:admin@skillsystem.rw" class="text-decoration-none">
                    <div class="d-flex flex-column align-items-center text-center p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r);">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--ss-warning-light);color:var(--ss-warning);display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.5rem;"><i class="fas fa-headset"></i></div>
                        <div style="font-size:0.82rem;font-weight:600;margin-bottom:2px;">Contact Support</div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">We're here to help</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ==================== ERROR DETAILS (ADMIN ONLY) ==================== -->
<?php if ($isAdmin): ?>
<div class="ss-card mt-4 ss-animate-fade-up" style="border-left:4px solid var(--ss-danger);">
    <div class="ss-card-header">
        <h3><i class="fas fa-bug text-danger"></i> Error Details <span class="ss-badge ss-badge-danger">Admin Only</span></h3>
        <button class="ss-btn ss-btn-ghost ss-btn-sm" onclick="const d=this.closest('.ss-card').querySelector('.ss-card-body');d.style.display=d.style.display==='none'?'block':'none';"><i class="fas fa-eye"></i> Toggle</button>
    </div>
    <div class="ss-card-body">
        <div class="table-responsive">
            <table class="ss-table">
                <tbody>
                    <tr><td style="font-weight:600;width:180px;">Error Code</td><td style="font-family:var(--ss-font-mono);">404 Not Found</td></tr>
                    <tr><td style="font-weight:600;">Requested URL</td><td style="font-family:var(--ss-font-mono);word-break:break-all;"><?= htmlspecialchars($currentUrl) ?></td></tr>
                    <tr><td style="font-weight:600;">Request Method</td><td style="font-family:var(--ss-font-mono);"><?= htmlspecialchars($requestMethod) ?></td></tr>
                    <tr><td style="font-weight:600;">Timestamp</td><td style="font-family:var(--ss-font-mono);"><?= htmlspecialchars($timestamp) ?></td></tr>
                    <tr><td style="font-weight:600;">Session ID</td><td style="font-family:var(--ss-font-mono);"><?= htmlspecialchars($sessionId) ?></td></tr>
                    <tr><td style="font-weight:600;">User ID</td><td style="font-family:var(--ss-font-mono);"><?= htmlspecialchars($userId ?? 'N/A') ?></td></tr>
                    <tr><td style="font-weight:600;">User Role</td><td><?= htmlspecialchars($userRole) ?></td></tr>
                    <tr><td style="font-weight:600;">User Agent</td><td style="font-size:0.78rem;"><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') ?></td></tr>
                    <tr><td style="font-weight:600;">IP Address</td><td style="font-family:var(--ss-font-mono);"><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Quick links search filter
function filterQuickLinks(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.quick-link-item').forEach(function(el) {
        var label = el.dataset.label || '';
        el.style.display = label.includes(q) ? '' : 'none';
    });
}
</script>
