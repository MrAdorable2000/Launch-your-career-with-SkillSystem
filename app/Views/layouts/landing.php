<?php
/**
 * SkillSystem — Landing Layout
 * Used for public homepage and verify pages.
 *
 * Defensive note: __() is normally defined in app/Helpers/functions.php (loaded
 * in index.php). If for some reason it isn't available (partial upgrade, custom
 * bootstrap), the fallback below keeps the page from crashing — strings will
 * simply render as their keys (English) until the full I18n system is in place.
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
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'SkillSystem — Connecting student talent with real-world opportunities through an intelligent career management platform.') ?>">
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

<nav class="ss-landing-nav">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <a class="navbar-brand" href="<?= URL::to('/') ?>">
                <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
                <span>Skill<span>System</span></span>
            </a>
            <button class="ss-btn ss-btn-ghost d-lg-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavContent">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse d-lg-flex align-items-center flex-grow-1 justify-content-center" id="landingNavContent">
                <ul class="navbar-nav d-flex flex-row flex-wrap justify-content-center gap-1 mb-2 mb-lg-0 list-unstyled">
                    <li class="nav-item"><a class="nav-link" href="<?= URL::to('/#features') ?>">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= URL::to('/#jobs') ?>">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= URL::to('/#companies') ?>">Companies</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= URL::to('/#universities') ?>">Universities</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= URL::to('/#community') ?>">Community</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= URL::to('/#faq') ?>">FAQ</a></li>
                </ul>
            </div>
            <div class="d-none d-lg-flex align-items-center gap-2">
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
                        $currentLang = \App\Helpers\Session::getLanguage();
                        $languages = [
                            'en' => ['name' => 'English', 'flag' => '🇬🇧'],
                            'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
                            'rw' => ['name' => 'Kinyarwanda', 'flag' => '🇷🇼'],
                            'sw' => ['name' => 'Swahili', 'flag' => '🇰🇪'],
                            'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
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
                <?php if (!empty($isLoggedIn)): ?>
                    <a href="<?= URL::to('student/dashboard') ?>" class="ss-btn ss-btn-ghost"><?= __('dashboard') ?></a>
                <?php else: ?>
                    <a href="<?= URL::to('login') ?>" class="ss-btn ss-btn-ghost"><?= __('sign_in') ?></a>
                    <a href="<?= URL::to('register') ?>" class="ss-btn ss-btn-gradient"><?= __('create_account') ?> <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?= $content ?? '' ?>

<footer class="ss-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
                    <span>SkillSystem</span>
                </div>
                <p style="font-size:0.875rem;line-height:1.6;max-width:340px;">Connecting student talent with real-world opportunities through an intelligent career management platform. Built for universities, employers, and students.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h5>Platform</h5>
                <a href="<?= URL::to('/#jobs') ?>">Find Jobs</a><br>
                <a href="<?= URL::to('/#internships') ?>">Internships</a><br>
                <a href="<?= URL::to('/#companies') ?>">Companies</a><br>
                <a href="<?= URL::to('/#universities') ?>">Universities</a><br>
                <a href="<?= URL::to('/#events') ?>">Events</a>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h5>For Students</h5>
                <a href="<?= URL::to('login') ?>">Build Portfolio</a><br>
                <a href="<?= URL::to('login') ?>">Resume Builder</a><br>
                <a href="<?= URL::to('login') ?>">Skill Assessment</a><br>
                <a href="<?= URL::to('login') ?>">Career Roadmap</a><br>
                <a href="<?= URL::to('login') ?>">Discussion Forum</a>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h5>Company</h5>
                <a href="#">About Us</a><br>
                <a href="#">Careers</a><br>
                <a href="#">Blog</a><br>
                <a href="#">Contact</a><br>
                <a href="#">Privacy Policy</a>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h5>Support</h5>
                <a href="#">Help Center</a><br>
                <a href="#">Documentation</a><br>
                <a href="#">Community</a><br>
                <a href="#">Status</a><br>
                <a href="#">Terms of Service</a>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>&copy; <?= date('Y') ?> SkillSystem. All rights reserved.</span>
            <span>Made with <i class="fas fa-heart" style="color:#EF4444;"></i> in Kigali, Rwanda</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.SS_URLS = { base: '<?= APP_URL ?>', setLanguage: '<?= URL::to('api/language/set') ?>' };
    window.SS_THEME = <?= json_encode(Theme::chartColors()) ?>;
    window.SS_I18N = { language_updated: <?= json_encode(__('language_updated')) ?>, language_update_failed: <?= json_encode(__('language_update_failed')) ?> };

    // Language switcher — saves via AJAX (works for guests + logged-in), then reloads
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
                document.cookie = 'ss_lang=' + lang + ';path=/;max-age=31536000';
                setTimeout(() => location.reload(), 300);
            } else {
                document.cookie = 'ss_lang=' + lang + ';path=/;max-age=31536000';
                location.reload();
            }
        } catch (e) {
            document.cookie = 'ss_lang=' + lang + ';path=/;max-age=31536000';
            location.reload();
        }
    }
</script>
<script src="<?= URL::asset('js/app.js') ?>"></script>
<script>
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const id = a.getAttribute('href');
            if (id === '#' || id.length < 2) return;
            const el = document.querySelector(id);
            if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    });
</script>
</body>
</html>
