<?php
/**
 * SkillSystem — Auth Layout
 * Two-panel split: gradient brand panel (left) + form panel (right).
 * Used by login, register, forgot-password.
 *
 * Defensive note: __() is normally defined in app/Helpers/functions.php (loaded
 * in index.php). If for some reason it isn't available (partial upgrade, custom
 * bootstrap), the fallback below keeps the page from crashing.
 */
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
    <title><?= htmlspecialchars($pageTitle ?? 'Sign in') . ' · ' . APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= URL::asset('css/app.css') ?>" rel="stylesheet">
    <style>
        .ss-auth-wrap { min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; }
        @media (max-width: 991.98px) { .ss-auth-wrap { grid-template-columns: 1fr; } .ss-auth-brand { display: none; } }
        .ss-auth-brand {
            background: var(--ss-grad-primary); color: #fff; padding: 3rem;
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .ss-auth-brand::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 50%), radial-gradient(circle at bottom left, rgba(6,182,212,0.3), transparent 50%);
        }
        .ss-auth-brand::after {
            content: ''; position: absolute; inset: 0; opacity: 0.1;
            background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.6) 1px, transparent 0); background-size: 28px 28px;
        }
        .ss-auth-brand > * { position: relative; z-index: 1; }
        .ss-auth-brand .brand-logo { display: inline-flex; align-items: center; gap: 0.75rem; font-size: 1.5rem; font-weight: 800; color: #fff; text-decoration: none; }
        .ss-auth-brand .brand-logo .icon { width: 44px; height: 44px; border-radius: var(--ss-r); background: rgba(255,255,255,0.15); display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; backdrop-filter: blur(8px); }
        .ss-auth-brand h1 { color: #fff; font-size: 2.5rem; font-weight: 900; line-height: 1.15; letter-spacing: -0.02em; margin-bottom: 1rem; }
        .ss-auth-brand p { color: rgba(255,255,255,0.85); font-size: 1.05rem; max-width: 420px; }
        .ss-auth-brand .feature-list { list-style: none; padding: 0; margin: 2rem 0 0; }
        .ss-auth-brand .feature-list li { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0; font-size: 0.95rem; color: rgba(255,255,255,0.9); }
        .ss-auth-brand .feature-list li i { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.15); display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; }
        .ss-auth-brand .testimonial { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--ss-r-lg); padding: 1.5rem; margin-top: 2rem; }
        .ss-auth-brand .testimonial p { margin-bottom: 0.75rem; font-size: 0.9rem; }
        .ss-auth-form { padding: 2rem 3rem; display: flex; flex-direction: column; justify-content: center; background: var(--ss-bg); }
        @media (max-width: 575.98px) { .ss-auth-form { padding: 2rem 1.5rem; } }
        .ss-auth-form-inner { max-width: 440px; margin: 0 auto; width: 100%; }
        .ss-auth-mobile-brand { display: none; align-items: center; justify-content: center; gap: 0.6rem; font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--ss-text); }
        @media (max-width: 991.98px) { .ss-auth-mobile-brand { display: flex; } }
        .ss-auth-mobile-brand .icon { width: 40px; height: 40px; border-radius: var(--ss-r-sm); background: var(--ss-grad-primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; }
        .ss-auth-top-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .ss-auth-top-actions a { font-size: 0.85rem; color: var(--ss-text-2); font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .ss-auth-top-actions a:hover { color: var(--ss-primary); }
        .ss-auth-form h2 { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; }
        .ss-auth-form .subtitle { color: var(--ss-text-2); margin-bottom: 2rem; font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="ss-auth-wrap">
    <div class="ss-auth-brand">
        <a href="<?= URL::to('/') ?>" class="brand-logo">
            <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            <span>SkillSystem</span>
        </a>
        <div>
            <h1>Launch your career with confidence.</h1>
            <p>Join thousands of students, employers, and universities building the future of work on SkillSystem.</p>
            <ul class="feature-list">
                <li><i class="fas fa-briefcase"></i> 5,000+ jobs & internships from top employers</li>
                <li><i class="fas fa-robot"></i> AI-powered resume scoring & career recommendations</li>
                <li><i class="fas fa-trophy"></i> Build a portfolio that gets you noticed</li>
                <li><i class="fas fa-shield-alt"></i> Verified certificates & QR code authentication</li>
            </ul>
            <div class="testimonial">
                <p>"SkillSystem helped me land my dream internship at Bank of Kigali within 3 weeks of joining. The AI resume score was a game changer!"</p>
                <div class="d-flex align-items-center gap-2">
                    <div class="ss-avatar ss-avatar-sm" style="background:rgba(255,255,255,0.2);">J</div>
                    <div>
                        <div style="font-weight:700;font-size:0.85rem;">Jean Pierre H.</div>
                        <div style="font-size:0.75rem;opacity:0.8;">Software Engineering Student</div>
                    </div>
                </div>
            </div>
        </div>
        <div style="font-size:0.78rem;color:rgba(255,255,255,0.6);">&copy; <?= date('Y') ?> SkillSystem · Made in Kigali, Rwanda</div>
    </div>

    <div class="ss-auth-form">
        <div class="ss-auth-form-inner">
            <div class="ss-auth-top-actions">
                <a href="<?= URL::to('/') ?>"><i class="fas fa-arrow-left"></i> Back to home</a>
                <button class="theme-toggle" data-theme-toggle title="Toggle theme">
                    <i class="fas fa-sun sun-icon"></i>
                    <i class="fas fa-moon moon-icon"></i>
                </button>
            </div>
            <div class="ss-auth-mobile-brand">
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                <span>Skill<span style="color:var(--ss-primary);">System</span></span>
            </div>
            <?= $flashMessage ?? '' ?>
            <?= $content ?? '' ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.SS_URLS = { base: '<?= APP_URL ?>' };
    window.SS_THEME = <?= json_encode(Theme::chartColors()) ?>;
</script>
<script src="<?= URL::asset('js/app.js') ?>"></script>
</body>
</html>
