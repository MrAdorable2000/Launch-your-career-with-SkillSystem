<?php
/**
 * SkillSystem — Main App Layout
 * Sidebar + Topbar + Content area.
 * Used by ALL authenticated pages (student, employer, admin, university, mentor).
 *
 * Defensive note: __() is normally defined in app/Helpers/functions.php (loaded
 * in index.php). If for some reason it isn't available (partial upgrade, custom
 * bootstrap), the fallback below keeps the page from crashing — strings will
 * simply render as their keys (English) until the full I18n system is in place.
 */
use App\Helpers\Session;
use App\Helpers\URL;
use App\Helpers\Theme;
use App\Helpers\CSRF;

// Safety net: define __() if it isn't already defined
if (!function_exists('__')) {
    function __(string $key, array $params = []): string {
        $text = $key;
        foreach ($params as $k => $v) {
            $text = str_replace('{' . $k . '}', (string) $v, $text);
        }
        return $text;
    }
}
?>
<!DOCTYPE html>
<html <?= Theme::htmlAttr() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= CSRF::token() ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') . ' · ' . APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= URL::asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>

<div class="ss-page-loader">
    <div class="loader-logo"><i class="fas fa-graduation-cap"></i></div>
    <div class="loader-spinner"></div>
</div>

<!-- ==================== SIDEBAR ==================== -->
<aside class="ss-sidebar" id="ssSidebar">
    <div class="ss-sidebar-brand">
        <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="brand-text">Skill<span>System</span></div>
        <button class="ss-btn ss-btn-ghost ms-auto d-lg-none p-1" data-sidebar-toggle style="color:rgba(255,255,255,0.6);">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="ss-sidebar-nav">
        <?php
        $current = URL::current();
        $isActive = function($path) use ($current) {
            return $current === $path ? 'active' : (strpos($current, $path) === 0 && $path !== '/' ? 'active' : '');
        };
        ?>

        <?php if (Session::isAdmin()): ?>
            <div class="nav-label"><?= __('overview') ?></div>
            <a href="<?= URL::to('admin/dashboard') ?>" class="nav-link <?= $isActive('/admin/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span><?= __('dashboard') ?></span></a>
            <a href="<?= URL::to('admin/analytics') ?>" class="nav-link <?= $isActive('/admin/analytics') ?>"><i class="fas fa-chart-bar"></i> <span><?= __('analytics') ?></span></a>
            <a href="<?= URL::to('admin/users') ?>" class="nav-link <?= $isActive('/admin/users') ?>"><i class="fas fa-users"></i> <span><?= __('users') ?></span></a>

            <div class="nav-label"><?= __('management') ?></div>
            <a href="<?= URL::to('admin/users') ?>?role=student" class="nav-link"><i class="fas fa-user-graduate"></i> <span><?= __('students') ?></span></a>
            <a href="<?= URL::to('admin/users') ?>?role=employer" class="nav-link"><i class="fas fa-building"></i> <span><?= __('employers') ?></span></a>
            <a href="<?= URL::to('admin/users') ?>?role=university" class="nav-link"><i class="fas fa-university"></i> <span><?= __('universities') ?></span></a>
            <a href="<?= URL::to('admin/users') ?>?role=mentor" class="nav-link"><i class="fas fa-chalkboard-teacher"></i> <span><?= __('mentors') ?></span></a>
            <a href="<?= URL::to('admin/jobs') ?>" class="nav-link <?= $isActive('/admin/jobs') ?>"><i class="fas fa-briefcase"></i> <span><?= __('jobs') ?></span></a>
            <a href="<?= URL::to('admin/internships') ?>" class="nav-link <?= $isActive('/admin/internships') ?>"><i class="fas fa-user-graduate"></i> <span><?= __('internships') ?></span></a>
            <a href="<?= URL::to('admin/applications') ?>" class="nav-link <?= $isActive('/admin/applications') ?>"><i class="fas fa-folder-open"></i> <span><?= __('applications') ?></span></a>
            <a href="<?= URL::to('admin/certificates') ?>" class="nav-link <?= $isActive('/admin/certificates') ?>"><i class="fas fa-certificate"></i> <span><?= __('certificates') ?></span>
            </a>
            <a href="<?= URL::to('admin/payments') ?>" class="nav-link <?= $isActive('/admin/payments') ?>"><i class="fas fa-credit-card"></i> <span><?= __('payments') ?></span></a>
            <a href="<?= URL::to('admin/reports') ?>" class="nav-link <?= $isActive('/admin/reports') ?>"><i class="fas fa-file-alt"></i> <span><?= __('reports') ?></span></a>

            <div class="nav-label"><?= __('system') ?></div>
            <a href="<?= URL::to('admin/audit-logs') ?>" class="nav-link <?= $isActive('/admin/audit-logs') ?>"><i class="fas fa-shield-alt"></i> <span><?= __('audit_logs') ?></span></a>
            <a href="<?= URL::to('admin/system-health') ?>" class="nav-link <?= $isActive('/admin/system-health') ?>"><i class="fas fa-heartbeat"></i> <span><?= __('system_health') ?></span></a>
            <a href="<?= URL::to('admin/security') ?>" class="nav-link <?= $isActive('/admin/security') ?>"><i class="fas fa-lock"></i> <span><?= __('security_center') ?></span>
            </a>
            <a href="<?= URL::to('admin/messages') ?>" class="nav-link <?= $isActive('/admin/messages') ?>"><i class="fas fa-envelope"></i> <span><?= __('messages') ?></span>
                <?php if (!empty($unreadMessages) && $unreadMessages > 0): ?><span class="badge-notif"><?= (int)$unreadMessages ?></span><?php endif; ?>
            </a>
            <a href="<?= URL::to('admin/notifications') ?>" class="nav-link <?= $isActive('/admin/notifications') ?>"><i class="fas fa-bell"></i> <span><?= __('notifications') ?></span>
                <?php if (!empty($unreadNotifications) && $unreadNotifications > 0): ?><span class="badge-notif"><?= (int)$unreadNotifications ?></span><?php endif; ?>
            </a>
            <a href="<?= URL::to('admin/email-sms') ?>" class="nav-link <?= $isActive('/admin/email-sms') ?>"><i class="fas fa-paper-plane"></i> <span><?= __('email_sms') ?></span></a>
            <a href="<?= URL::to('admin/backup') ?>" class="nav-link <?= $isActive('/admin/backup') ?>"><i class="fas fa-database"></i> <span><?= __('backup_restore') ?></span></a>
            <a href="<?= URL::to('admin/maintenance') ?>" class="nav-link <?= $isActive('/admin/maintenance') ?>"><i class="fas fa-tools"></i> <span><?= __('maintenance') ?></span></a>
            <a href="<?= URL::to('admin/settings') ?>" class="nav-link <?= $isActive('/admin/settings') ?>"><i class="fas fa-cog"></i> <span><?= __('settings') ?></span></a>
            <a href="<?= URL::to('admin/homepage') ?>" class="nav-link <?= $isActive('/admin/homepage') ?>"><i class="fas fa-home"></i> <span><?= __('homepage_manager') ?></span></a>

        <?php elseif (Session::isStudent()): ?>
            <div class="nav-label"><?= __('main') ?></div>
            <a href="<?= URL::to('student/dashboard') ?>" class="nav-link <?= $isActive('/student/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span><?= __('dashboard') ?></span></a>
            <a href="<?= URL::to('student/profile') ?>" class="nav-link <?= $isActive('/student/profile') ?>"><i class="fas fa-user-circle"></i> <span><?= __('my_profile_nav') ?></span></a>
            <a href="<?= URL::to('student/jobs') ?>" class="nav-link <?= $isActive('/student/jobs') ?>"><i class="fas fa-briefcase"></i> <span><?= __('browse_jobs') ?></span></a>
            <a href="<?= URL::to('student/applications') ?>" class="nav-link <?= $isActive('/student/applications') ?>"><i class="fas fa-folder-open"></i> <span><?= __('applications') ?></span></a>
            <div class="nav-label"><?= __('career_tools') ?></div>
            <a href="<?= URL::to('student/portfolio') ?>" class="nav-link <?= $isActive('/student/portfolio') ?>"><i class="fas fa-folder-plus"></i> <span><?= __('portfolio') ?></span></a>
            <a href="<?= URL::to('student/resume') ?>" class="nav-link <?= $isActive('/student/resume') ?>"><i class="fas fa-file-alt"></i> <span><?= __('resume') ?></span></a>
            <a href="<?= URL::to('student/ai-score') ?>" class="nav-link <?= $isActive('/student/ai-score') ?>"><i class="fas fa-robot"></i> <span><?= __('ai_resume_score') ?></span></a>
            <a href="<?= URL::to('student/leaderboard') ?>" class="nav-link <?= $isActive('/student/leaderboard') ?>"><i class="fas fa-trophy"></i> <span><?= __('leaderboard') ?></span></a>
            <a href="<?= URL::to('student/badges') ?>" class="nav-link <?= $isActive('/student/badges') ?>"><i class="fas fa-medal"></i> <span><?= __('badges') ?></span></a>
            <a href="<?= URL::to('student/roadmap') ?>" class="nav-link <?= $isActive('/student/roadmap') ?>"><i class="fas fa-road"></i> <span><?= __('career_roadmap') ?></span></a>
            <div class="nav-label"><?= __('community') ?></div>
            <a href="<?= URL::to('student/events') ?>" class="nav-link <?= $isActive('/student/events') ?>"><i class="fas fa-calendar-alt"></i> <span><?= __('events') ?></span></a>
            <a href="<?= URL::to('student/forum') ?>" class="nav-link <?= $isActive('/student/forum') ?>"><i class="fas fa-comments"></i> <span><?= __('forum') ?></span></a>
            <a href="<?= URL::to('student/messages') ?>" class="nav-link <?= $isActive('/student/messages') ?>"><i class="fas fa-envelope"></i> <span><?= __('messages') ?></span><?php if (!empty($unreadMessages)): ?><span class="badge-notif"><?= (int)$unreadMessages ?></span><?php endif; ?></a>
            <a href="<?= URL::to('student/settings') ?>" class="nav-link <?= $isActive('/student/settings') ?>"><i class="fas fa-cog"></i> <span><?= __('settings') ?></span></a>

        <?php elseif (Session::isEmployer()): ?>
            <div class="nav-label"><?= __('main') ?></div>
            <a href="<?= URL::to('employer/dashboard') ?>" class="nav-link <?= $isActive('/employer/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span><?= __('dashboard') ?></span></a>
            <a href="<?= URL::to('employer/post-job') ?>" class="nav-link <?= $isActive('/employer/post-job') ?>"><i class="fas fa-plus-circle"></i> <span><?= __('post_a_job') ?></span></a>
            <a href="<?= URL::to('employer/jobs') ?>" class="nav-link <?= $isActive('/employer/jobs') ?>"><i class="fas fa-briefcase"></i> <span><?= __('my_jobs') ?></span></a>
            <div class="nav-label"><?= __('opportunities') ?></div>
            <a href="<?= URL::to('employer/internships') ?>" class="nav-link <?= $isActive('/employer/internships') ?>"><i class="fas fa-user-graduate"></i> <span><?= __('internships') ?></span></a>
            <a href="<?= URL::to('employer/freelance') ?>" class="nav-link <?= $isActive('/employer/freelance') ?>"><i class="fas fa-laptop-code"></i> <span><?= __('freelance') ?></span></a>
            <div class="nav-label"><?= __('account') ?></div>
            <a href="<?= URL::to('employer/messages') ?>" class="nav-link <?= $isActive('/employer/messages') ?>"><i class="fas fa-envelope"></i> <span><?= __('messages') ?></span><?php if (!empty($unreadMessages)): ?><span class="badge-notif"><?= (int)$unreadMessages ?></span><?php endif; ?></a>
            <a href="<?= URL::to('employer/company') ?>" class="nav-link <?= $isActive('/employer/company') ?>"><i class="fas fa-building"></i> <span><?= __('company') ?></span></a>
            <a href="<?= URL::to('employer/settings') ?>" class="nav-link <?= $isActive('/employer/settings') ?>"><i class="fas fa-cog"></i> <span><?= __('settings') ?></span></a>

        <?php elseif (Session::isUniversity()): ?>
            <div class="nav-label"><?= __('main') ?></div>
            <a href="<?= URL::to('university/dashboard') ?>" class="nav-link <?= $isActive('/university/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span><?= __('dashboard') ?></span></a>
            <a href="<?= URL::to('university/students') ?>" class="nav-link <?= $isActive('/university/students') ?>"><i class="fas fa-users"></i> <span><?= __('students') ?></span></a>
            <a href="<?= URL::to('university/reports') ?>" class="nav-link <?= $isActive('/university/reports') ?>"><i class="fas fa-chart-bar"></i> <span><?= __('reports') ?></span></a>
            <a href="<?= URL::to('university/messages') ?>" class="nav-link <?= $isActive('/university/messages') ?>"><i class="fas fa-envelope"></i> <span><?= __('messages') ?></span><?php if (!empty($unreadMessages)): ?><span class="badge-notif"><?= (int)$unreadMessages ?></span><?php endif; ?></a>

        <?php elseif (Session::isMentor()): ?>
            <div class="nav-label"><?= __('main') ?></div>
            <a href="<?= URL::to('mentor/dashboard') ?>" class="nav-link <?= $isActive('/mentor/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span><?= __('dashboard') ?></span></a>
            <a href="<?= URL::to('mentor/sessions') ?>" class="nav-link <?= $isActive('/mentor/sessions') ?>"><i class="fas fa-calendar-check"></i> <span><?= __('sessions') ?></span></a>
            <a href="<?= URL::to('mentor/messages') ?>" class="nav-link <?= $isActive('/mentor/messages') ?>"><i class="fas fa-envelope"></i> <span><?= __('messages') ?></span><?php if (!empty($unreadMessages)): ?><span class="badge-notif"><?= (int)$unreadMessages ?></span><?php endif; ?></a>
        <?php endif; ?>
    </nav>

    <div class="ss-sidebar-footer">
        <div class="ss-sidebar-user">
            <div class="avatar"><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></div>
            <div class="user-info flex-grow-1" style="min-width:0;">
                <div class="user-name text-truncate"><?= htmlspecialchars($userName ?? 'User') ?></div>
                <div class="user-role text-capitalize"><?= htmlspecialchars($userRole ?? '') ?></div>
            </div>
            <a href="<?= URL::to('logout') ?>" class="ss-btn ss-btn-ghost p-1" title="<?= __('logout') ?>" style="color:rgba(255,255,255,0.6);">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>

<!-- ==================== MAIN ==================== -->
<div class="ss-main">
    <header class="ss-topbar">
        <div class="ss-topbar-left">
            <button class="ss-btn ss-btn-ghost p-2 d-lg-none" data-sidebar-toggle><i class="fas fa-bars"></i></button>
            <button class="ss-btn ss-btn-ghost p-2 d-none d-lg-inline-flex" data-sidebar-collapse title="<?= __('collapse') ?>"><i class="fas fa-outdent"></i></button>
            <div class="d-none d-md-block" style="font-size:0.95rem;font-weight:700;"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
        </div>
        <div class="ss-topbar-right">
            <div class="ss-topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="<?= __('search') ?>">
            </div>
            <button class="theme-toggle" data-theme-toggle title="<?= __('toggle_theme') ?>">
                <i class="fas fa-sun sun-icon"></i>
                <i class="fas fa-moon moon-icon"></i>
            </button>
            <div class="dropdown">
                <button class="theme-toggle" data-bs-toggle="dropdown" title="<?= __('language') ?>">
                    <i class="fas fa-globe"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header"><strong><?= __('language') ?></strong></div>
                    <div class="dropdown-divider"></div>
                    <?php
                    $currentLang = Session::getLanguage();
                    $languages = [
                        'en' => ['name' => 'English', 'flag' => '🇬🇧', 'color' => 'primary'],
                        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'color' => 'info'],
                        'rw' => ['name' => 'Kinyarwanda', 'flag' => '🇷🇼', 'color' => 'success'],
                        'sw' => ['name' => 'Swahili', 'flag' => '🇰🇪', 'color' => 'warning'],
                        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦', 'color' => 'danger'],
                    ];
                    foreach ($languages as $code => $lang):
                        $isActive = ($currentLang === $code);
                    ?>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="setLanguage('<?= $code ?>');return false;" style="<?= $isActive ? 'background:var(--ss-primary-light);' : '' ?>">
                        <span style="font-size:1.1rem;"><?= $lang['flag'] ?></span>
                        <span><?= $lang['name'] ?></span>
                        <?php if ($isActive): ?><span class="ss-badge ss-badge-primary ms-auto"><i class="fas fa-check"></i></span><?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="dropdown">
                <button class="theme-toggle position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                    data-notif-endpoint="<?= URL::to('api/notifications') ?>"
                    data-notif-mark-read="<?= URL::to('api/notifications/read') ?>" title="<?= __('notifications') ?>">
                    <i class="fas fa-bell"></i>
                    <span data-notif-badge class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:var(--ss-danger);color:#fff;font-size:0.65rem;min-width:18px;height:18px;display:<?= !empty($unreadNotifications) ? 'inline-flex' : 'none' ?>;align-items:center;justify-content:center;padding:0 5px;">
                        <?= (int)($unreadNotifications ?? 0) ?>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 notif-dropdown">
                    <div class="notif-dropdown-header">
                        <strong><?= __('notifications') ?></strong>
                        <a href="#" data-mark-all-read style="font-size:0.78rem;color:var(--ss-primary);font-weight:600;text-decoration:none;"><?= __('mark_all_read') ?></a>
                    </div>
                    <div class="notif-dropdown-body">
                        <?php if (!empty($recentNotifications)): ?>
                            <?php foreach ($recentNotifications as $notif): ?>
                                <div class="notif-item <?= empty($notif['read_at']) ? 'unread' : '' ?>" data-notif-id="<?= (int)$notif['id'] ?>">
                                    <div class="notif-dot"></div>
                                    <div class="notif-icon"><i class="fas fa-bell"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="notif-title"><?= htmlspecialchars($notif['title']) ?></div>
                                        <div class="notif-msg"><?= htmlspecialchars($notif['message']) ?></div>
                                        <div class="notif-time"><?= htmlspecialchars(date('M j, g:i a', strtotime($notif['created_at']))) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding:2rem 1rem;color:var(--ss-text-3);">
                                <i class="fas fa-bell-slash mb-2 d-block" style="font-size:1.5rem;opacity:0.4;"></i>
                                <div style="font-size:0.85rem;"><?= __('no_notifications') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="<?= URL::to(($userRole ?? 'student') . '/messages') ?>" class="theme-toggle position-relative" title="<?= __('messages') ?>">
                <i class="fas fa-envelope"></i>
                <?php if (!empty($unreadMessages)): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:var(--ss-warning);color:#fff;font-size:0.65rem;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px;">
                        <?= (int)$unreadMessages ?>
                    </span>
                <?php endif; ?>
            </a>
            <div class="dropdown">
                <button class="ss-btn ss-btn-ghost d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
                    <div class="ss-avatar ss-avatar-sm"><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></div>
                    <div class="d-none d-md-block text-start">
                        <div style="font-size:0.82rem;font-weight:600;line-height:1.2;"><?= htmlspecialchars($userName ?? 'User') ?></div>
                        <div style="font-size:0.7rem;color:var(--ss-text-3);text-transform:capitalize;"><?= htmlspecialchars($userRole ?? '') ?></div>
                    </div>
                    <i class="fas fa-chevron-down d-none d-md-block" style="font-size:0.65rem;color:var(--ss-text-3);"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">
                        <div style="font-weight:700;font-size:0.9rem;"><?= htmlspecialchars($userName ?? '') ?></div>
                        <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars(Session::get('userEmail') ?? '') ?></div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <?php if (Session::isStudent()): ?>
                        <a class="dropdown-item" href="<?= URL::to('student/profile') ?>"><i class="fas fa-user"></i> <?= __('my_profile') ?></a>
                        <a class="dropdown-item" href="<?= URL::to('student/settings') ?>"><i class="fas fa-cog"></i> <?= __('settings') ?></a>
                    <?php elseif (Session::isEmployer()): ?>
                        <a class="dropdown-item" href="<?= URL::to('employer/company') ?>"><i class="fas fa-building"></i> <?= __('company') ?></a>
                        <a class="dropdown-item" href="<?= URL::to('employer/settings') ?>"><i class="fas fa-cog"></i> <?= __('settings') ?></a>
                    <?php elseif (Session::isAdmin()): ?>
                        <a class="dropdown-item" href="<?= URL::to('admin/settings') ?>"><i class="fas fa-cog"></i> <?= __('settings') ?></a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= URL::to('logout') ?>" style="color:var(--ss-danger);"><i class="fas fa-sign-out-alt"></i> <?= __('sign_out') ?></a>
                </div>
            </div>
        </div>
    </header>

    <main class="ss-content">
        <?= $flashMessage ?? '' ?>
        <?= $content ?? '' ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.SS_URLS = { base: '<?= APP_URL ?>', notifications: '<?= URL::to('api/notifications') ?>', notificationsMarkRead: '<?= URL::to('api/notifications/read') ?>', messagesUnread: '<?= URL::to('api/messages/unread') ?>', setLanguage: '<?= URL::to('api/language/set') ?>' };
    window.SS_THEME = <?= json_encode(Theme::chartColors()) ?>;
    window.SS_LANG = '<?= Session::getLanguage() ?>';
    window.SS_I18N = { language_updated: <?= json_encode(__('language_updated')) ?>, language_update_failed: <?= json_encode(__('language_update_failed')) ?> };

    // Language switcher — saves to database via AJAX, then reloads
    async function setLanguage(lang) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        try {
            const res = await fetch(window.SS_URLS.setLanguage, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ language: lang, _token: csrfToken }),
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (data.success) {
                // Set cookie as backup
                document.cookie = 'ss_lang=' + lang + ';path=/;max-age=31536000';
                // Show toast (use server-rendered translated strings if available)
                var msg = (window.SS_I18N && window.SS_I18N.language_updated) ? window.SS_I18N.language_updated : 'Language updated successfully!';
                if (window.ssToast) ssToast.show(msg, 'success');
                // Reload after short delay
                setTimeout(() => location.reload(), 600);
            } else {
                var failMsg = (window.SS_I18N && window.SS_I18N.language_update_failed) ? window.SS_I18N.language_update_failed : 'Failed to update language';
                if (window.ssToast) ssToast.show(data.message || failMsg, 'error');
            }
        } catch (e) {
            // Fallback: just set cookie and reload
            document.cookie = 'ss_lang=' + lang + ';path=/;max-age=31536000';
            location.reload();
        }
    }
</script>
<script src="<?= URL::asset('js/app.js') ?>"></script>
</body>
</html>
