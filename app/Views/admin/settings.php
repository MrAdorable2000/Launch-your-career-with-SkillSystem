<?php
/**
 * Admin — Settings (Premium redesigned, Stripe-quality)
 *
 * Data passed from AdminController::settings():
 *   $settings — array of rows: id, key, value, type
 *
 * Form: POST /admin/settings — every key starting with "setting_" is persisted.
 *       Field name MUST be setting_{key}, e.g. setting_site_name.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Settings';

$settings = $settings ?? [];

// Index settings by key for easy access
$indexed = [];
foreach ($settings as $s) {
    $indexed[$s['key']] = $s;
}
$getVal = function($key, $default = '') use ($indexed) {
    return $indexed[$key]['value'] ?? $default;
};

// Group settings into tabs (General, Branding, Email, Payments, Features, Security, System Info)
$tabs = [
    'general'   => ['label' => 'General',       'icon' => 'fa-cog'],
    'branding'  => ['label' => 'Branding',      'icon' => 'fa-palette'],
    'email'     => ['label' => 'Email & SMS',   'icon' => 'fa-envelope'],
    'payments'  => ['label' => 'Payments',      'icon' => 'fa-credit-card'],
    'features'  => ['label' => 'Features',      'icon' => 'fa-toggle-on'],
    'security'  => ['label' => 'Security',      'icon' => 'fa-shield-alt'],
    'system'    => ['label' => 'System Info',   'icon' => 'fa-server'],
];

// Map known keys to tab groups (defaults to general)
$keyToTab = [
    'site_name'              => 'general',
    'site_tagline'           => 'general',
    'site_url'               => 'general',
    'support_email'          => 'general',
    'timezone'               => 'general',
    'language'               => 'general',
    'max_file_upload_size'   => 'general',

    'smtp_host'              => 'email',
    'smtp_port'              => 'email',
    'smtp_username'          => 'email',
    'smtp_password'          => 'email',
    'smtp_encryption'        => 'email',
    'mail_from_address'      => 'email',
    'mail_from_name'         => 'email',

    'currency'               => 'payments',
    'payment_provider'       => 'payments',
    'stripe_public_key'      => 'payments',
    'stripe_secret_key'      => 'payments',
    'paypal_client_id'       => 'payments',
    'paypal_client_secret'   => 'payments',
    'subscription_price'     => 'payments',

    'enable_jobs'            => 'features',
    'enable_internships'     => 'features',
    'enable_freelance'       => 'features',
    'enable_mentorship'      => 'features',
    'enable_forum'           => 'features',
    'enable_certificates'    => 'features',
    'enable_ai_resume'       => 'features',
    'maintenance_mode'       => 'features',

    'session_timeout'        => 'security',
    'password_min_length'    => 'security',
    'password_require_special' => 'security',
    'max_login_attempts'     => 'security',
    'enable_2fa'             => 'security',
    'recaptcha_site_key'     => 'security',
    'recaptcha_secret_key'   => 'security',
];

// Field definitions (label, type, tab) — used for known keys; unknown ones fall back to text input in General
$fieldDefs = [
    'site_name'              => ['label' => 'Site Name',             'type' => 'text',  'tab' => 'general'],
    'site_description'       => ['label' => 'Site Description',      'type' => 'textarea','tab' => 'general'],
    'site_tagline'           => ['label' => 'Site Tagline',          'type' => 'text',  'tab' => 'general'],
    'site_url'               => ['label' => 'Site URL',              'type' => 'url',   'tab' => 'general'],
    'site_email'             => ['label' => 'Site Email',            'type' => 'email', 'tab' => 'general'],
    'support_email'          => ['label' => 'Support Email',         'type' => 'email', 'tab' => 'general'],
    'timezone'               => ['label' => 'Default Timezone',      'type' => 'select','tab' => 'general', 'options' => ['Africa/Kigali' => 'Africa/Kigali (UTC+2)', 'UTC' => 'UTC', 'Europe/London' => 'Europe/London', 'America/New_York' => 'America/New_York', 'Asia/Dubai' => 'Asia/Dubai']],
    'language'               => ['label' => 'Default Language',      'type' => 'select','tab' => 'general', 'options' => ['en' => 'English', 'fr' => 'Français', 'rw' => 'Kinyarwanda', 'sw' => 'Swahili', 'ar' => 'العربية']],
    'default_currency'       => ['label' => 'Default Currency',      'type' => 'select','tab' => 'general', 'options' => ['RWF' => 'RWF (Rwandan Franc)', 'USD' => 'USD (US Dollar)', 'EUR' => 'EUR (Euro)', 'KES' => 'KES (Kenyan Shilling)', 'UGX' => 'UGX (Ugandan Shilling)']],
    'max_file_upload_size'   => ['label' => 'Max File Upload (bytes)','type' => 'number','tab' => 'general', 'hint' => '5242880 = 5MB'],
    'allowed_file_types'     => ['label' => 'Allowed File Types',    'type' => 'text',  'tab' => 'general', 'hint' => 'Comma-separated extensions'],
    'posts_per_page'         => ['label' => 'Items Per Page',        'type' => 'number','tab' => 'general'],

    // Branding & Social Media
    'site_logo'              => ['label' => 'Logo URL',              'type' => 'text',  'tab' => 'branding', 'hint' => 'Full URL to your logo image'],
    'site_favicon'           => ['label' => 'Favicon URL',           'type' => 'text',  'tab' => 'branding', 'hint' => 'Full URL to your favicon (.ico or .png)'],
    'site_keywords'          => ['label' => 'SEO Keywords',          'type' => 'textarea','tab' => 'branding', 'hint' => 'Comma-separated keywords for SEO'],
    'contact_phone'          => ['label' => 'Contact Phone',         'type' => 'text',  'tab' => 'branding'],
    'contact_address'        => ['label' => 'Contact Address',       'type' => 'text',  'tab' => 'branding'],
    'footer_text'            => ['label' => 'Footer Text',           'type' => 'textarea','tab' => 'branding'],
    'social_facebook'        => ['label' => 'Facebook URL',          'type' => 'url',   'tab' => 'branding'],
    'social_twitter'         => ['label' => 'Twitter/X URL',         'type' => 'url',   'tab' => 'branding'],
    'social_linkedin'        => ['label' => 'LinkedIn URL',          'type' => 'url',   'tab' => 'branding'],
    'social_instagram'       => ['label' => 'Instagram URL',         'type' => 'url',   'tab' => 'branding'],
    'social_youtube'         => ['label' => 'YouTube URL',           'type' => 'url',   'tab' => 'branding'],
    'social_whatsapp'        => ['label' => 'WhatsApp Number',       'type' => 'text',  'tab' => 'branding'],
    'social_telegram'        => ['label' => 'Telegram URL',          'type' => 'url',   'tab' => 'branding'],
    'social_github'          => ['label' => 'GitHub URL',            'type' => 'url',   'tab' => 'branding'],
    'google_analytics_id'    => ['label' => 'Google Analytics ID',   'type' => 'text',  'tab' => 'branding', 'hint' => 'e.g. G-XXXXXXXXXX'],
    'facebook_pixel_id'      => ['label' => 'Facebook Pixel ID',     'type' => 'text',  'tab' => 'branding'],

    'smtp_host'              => ['label' => 'SMTP Host',             'type' => 'text',  'tab' => 'email'],
    'smtp_port'              => ['label' => 'SMTP Port',             'type' => 'number','tab' => 'email'],
    'smtp_username'          => ['label' => 'SMTP Username',         'type' => 'text',  'tab' => 'email'],
    'smtp_password'          => ['label' => 'SMTP Password',         'type' => 'password','tab' => 'email'],
    'smtp_encryption'        => ['label' => 'SMTP Encryption',       'type' => 'select','tab' => 'email', 'options' => ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL']],
    'mail_from_address'      => ['label' => 'From Address',          'type' => 'email', 'tab' => 'email'],
    'mail_from_name'         => ['label' => 'From Name',             'type' => 'text',  'tab' => 'email'],

    'currency'               => ['label' => 'Currency Code',         'type' => 'text',  'tab' => 'payments'],
    'payment_provider'       => ['label' => 'Payment Provider',      'type' => 'select','tab' => 'payments', 'options' => ['' => 'None', 'stripe' => 'Stripe', 'paypal' => 'PayPal', 'both' => 'Both']],
    'stripe_public_key'      => ['label' => 'Stripe Public Key',     'type' => 'text',  'tab' => 'payments'],
    'stripe_secret_key'      => ['label' => 'Stripe Secret Key',     'type' => 'password','tab' => 'payments'],
    'paypal_client_id'       => ['label' => 'PayPal Client ID',      'type' => 'text',  'tab' => 'payments'],
    'paypal_client_secret'   => ['label' => 'PayPal Client Secret',  'type' => 'password','tab' => 'payments'],
    'subscription_price'     => ['label' => 'Subscription Price',    'type' => 'number','tab' => 'payments'],
    'free_plan_max_applications' => ['label' => 'Free Plan: Max Applications', 'type' => 'number', 'tab' => 'payments'],
    'basic_plan_price'       => ['label' => 'Basic Plan Price',      'type' => 'number','tab' => 'payments'],
    'premium_plan_price'     => ['label' => 'Premium Plan Price',    'type' => 'number','tab' => 'payments'],
    'enterprise_plan_price'  => ['label' => 'Enterprise Plan Price', 'type' => 'number','tab' => 'payments'],

    'enable_jobs'            => ['label' => 'Enable Jobs Module',         'type' => 'switch', 'tab' => 'features'],
    'enable_internships'     => ['label' => 'Enable Internships Module',  'type' => 'switch', 'tab' => 'features'],
    'enable_freelance'       => ['label' => 'Enable Freelance Marketplace','type' => 'switch', 'tab' => 'features'],
    'enable_mentorship'      => ['label' => 'Enable Mentorship Program',  'type' => 'switch', 'tab' => 'features'],
    'enable_forum'           => ['label' => 'Enable Community Forum',     'type' => 'switch', 'tab' => 'features'],
    'enable_certificates'    => ['label' => 'Enable Certificates',        'type' => 'switch', 'tab' => 'features'],
    'enable_ai_resume'       => ['label' => 'Enable AI Resume Scoring',   'type' => 'switch', 'tab' => 'features'],
    'enable_registration'    => ['label' => 'Enable User Registration',   'type' => 'switch', 'tab' => 'features'],
    'enable_dark_mode'       => ['label' => 'Enable Dark Mode',           'type' => 'switch', 'tab' => 'features'],
    'require_email_verification' => ['label' => 'Require Email Verification','type' => 'switch','tab' => 'features'],
    'maintenance_mode'       => ['label' => 'Maintenance Mode',           'type' => 'switch', 'tab' => 'features'],

    'session_timeout'        => ['label' => 'Session Timeout (minutes)',  'type' => 'number', 'tab' => 'security'],
    'password_min_length'    => ['label' => 'Minimum Password Length',    'type' => 'number', 'tab' => 'security'],
    'password_require_special' => ['label' => 'Require Special Characters','type' => 'switch', 'tab' => 'security'],
    'max_login_attempts'     => ['label' => 'Max Login Attempts',         'type' => 'number', 'tab' => 'security'],
    'enable_2fa'             => ['label' => 'Enable Two-Factor Auth',     'type' => 'switch', 'tab' => 'security'],
    'recaptcha_site_key'     => ['label' => 'reCAPTCHA Site Key',         'type' => 'text',   'tab' => 'security'],
    'recaptcha_secret_key'   => ['label' => 'reCAPTCHA Secret Key',       'type' => 'password','tab' => 'security'],
];

// Bucket settings by tab — ensure every key ends up in a tab (default: general)
$bucketed = ['general' => [], 'branding' => [], 'email' => [], 'payments' => [], 'features' => [], 'security' => [], 'system' => []];

// Default values for ALL settings (used as fallback if DB is empty)
$defaultValues = [
    'site_name' => 'SkillSystem', 'site_description' => 'Student Skills, Internship & Career Management System',
    'site_tagline' => 'Connect. Learn. Succeed.', 'site_url' => APP_URL, 'site_email' => 'noreply@skillsystem.rw',
    'site_logo' => '', 'site_favicon' => '', 'site_keywords' => 'skills, education, jobs, careers, internships, Rwanda',
    'support_email' => 'support@skillsystem.rw', 'contact_phone' => '+250788000001', 'contact_address' => 'Kigali, Rwanda',
    'footer_text' => 'SkillSystem — Connecting student talent with real-world opportunities.',
    'timezone' => 'Africa/Kigali', 'language' => 'en', 'default_currency' => 'RWF',
    'max_file_upload_size' => '5242880', 'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx', 'posts_per_page' => '10',
    'enable_registration' => '1', 'enable_dark_mode' => '1', 'require_email_verification' => '0', 'maintenance_mode' => '0',
    'enable_jobs' => '1', 'enable_internships' => '1', 'enable_freelance' => '1', 'enable_mentorship' => '1',
    'enable_forum' => '1', 'enable_certificates' => '1', 'enable_ai_resume' => '1',
    'free_plan_max_applications' => '10', 'basic_plan_price' => '20000', 'premium_plan_price' => '50000', 'enterprise_plan_price' => '150000',
    'smtp_host' => 'smtp.gmail.com', 'smtp_port' => '587', 'smtp_username' => 'noreply@skillsystem.rw',
    'smtp_password' => '', 'smtp_encryption' => 'tls', 'mail_from_address' => 'noreply@skillsystem.rw', 'mail_from_name' => 'SkillSystem',
    'session_timeout' => '120', 'password_min_length' => '8', 'password_require_special' => '0',
    'max_login_attempts' => '5', 'enable_2fa' => '0', 'recaptcha_site_key' => '', 'recaptcha_secret_key' => '',
    'social_facebook' => 'https://facebook.com/skillsystem', 'social_twitter' => 'https://twitter.com/skillsystem',
    'social_linkedin' => 'https://linkedin.com/company/skillsystem', 'social_instagram' => 'https://instagram.com/skillsystem',
    'social_youtube' => 'https://youtube.com/@skillsystem', 'social_whatsapp' => '+250788000001',
    'social_telegram' => '', 'social_github' => 'https://github.com/skillsystem',
    'google_analytics_id' => '', 'facebook_pixel_id' => '',
    'currency' => 'RWF', 'payment_provider' => '', 'stripe_public_key' => '', 'stripe_secret_key' => '',
    'paypal_client_id' => '', 'paypal_client_secret' => '', 'subscription_price' => '20000',
];

// Merge DB values with defaults — every fieldDefs key will appear even if not in DB
$allKeys = array_unique(array_merge(array_keys($indexed), array_keys($fieldDefs), array_keys($defaultValues)));
foreach ($allKeys as $key) {
    $def = $fieldDefs[$key] ?? null;
    if (!$def) continue; // Skip keys without field definitions
    $tab = $def['tab'] ?? ($keyToTab[$key] ?? 'general');
    if ($tab === 'system') continue; // System tab is rendered manually
    if (!isset($bucketed[$tab])) $tab = 'general';
    $val = $indexed[$key]['value'] ?? $defaultValues[$key] ?? '';
    $bucketed[$tab][] = [
        'key'   => $key,
        'value' => $val,
        'def'   => $def,
    ];
}
?>
<?= Component::pageHeader(
    'System Settings ⚙️',
    '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Settings</span>'
) ?>

<div class="ss-card ss-animate-fade-up">
    <div class="ss-card-body" style="padding:0;">
        <!-- Tabs -->
        <div class="ss-tabs" style="padding:0.5rem 1.25rem 0;border-bottom:1px solid var(--ss-border);">
            <?php foreach ($tabs as $tabKey => $tabMeta): ?>
                <button class="ss-tab <?= $tabKey === 'general' ? 'active' : '' ?>" data-tab-target="<?= htmlspecialchars($tabKey) ?>">
                    <i class="fas <?= htmlspecialchars($tabMeta['icon']) ?>"></i>
                    <span class="d-none d-md-inline"><?= htmlspecialchars($tabMeta['label']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Tab Panes -->
        <form method="POST" action="<?= URL::to('admin/settings/update') ?>" id="settingsForm">
            <?= $csrfField ?? '' ?>

            <?php foreach ($tabs as $tabKey => $tabMeta): ?>
                <?php if ($tabKey === 'system') continue; // Skip system tab — it's rendered manually below ?>
            <div class="ss-tab-pane <?= $tabKey === 'general' ? 'active' : '' ?>" data-tab="<?= htmlspecialchars($tabKey) ?>" style="padding:1.5rem;">
                <?php if (empty($bucketed[$tabKey])): ?>
                    <?= Component::emptyState([
                        'icon'  => 'fa-inbox',
                        'title' => 'No settings in this section',
                        'desc'  => 'Settings for ' . strtolower($tabMeta['label']) . ' will appear here once configured in the database.',
                    ]) ?>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($bucketed[$tabKey] as $item):
                            $key   = $item['key'];
                            $val   = $item['value'];
                            $def   = $item['def'];
                            $type  = $def['type'] ?? 'text';
                            $label = $def['label'] ?? ucfirst(str_replace('_', ' ', $key));
                            $fieldName = 'setting_' . $key;
                            $fieldId   = 'setting_' . $key;
                        ?>
                        <div class="col-md-6">
                            <?php switch ($type):
                                case 'textarea': ?>
                                    <div class="ss-form-group">
                                        <label class="ss-form-label" for="<?= htmlspecialchars($fieldId) ?>"><?= htmlspecialchars($label) ?></label>
                                        <textarea class="ss-textarea" name="<?= htmlspecialchars($fieldName) ?>" id="<?= htmlspecialchars($fieldId) ?>" rows="3"><?= htmlspecialchars($val) ?></textarea>
                                    </div>
                                    <?php break;
                                case 'select':
                                    $opts = $def['options'] ?? []; ?>
                                    <div class="ss-form-group ss-float">
                                        <select name="<?= htmlspecialchars($fieldName) ?>" id="<?= htmlspecialchars($fieldId) ?>">
                                            <?php foreach ($opts as $k => $v): ?>
                                                <option value="<?= htmlspecialchars($k) ?>" <?= ((string)$val === (string)$k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="<?= htmlspecialchars($fieldId) ?>"><?= htmlspecialchars($label) ?></label>
                                    </div>
                                    <?php break;
                                case 'switch': ?>
                                    <div class="ss-form-group">
                                        <label class="ss-form-label"><?= htmlspecialchars($label) ?></label>
                                        <label class="ss-switch">
                                            <input type="checkbox" name="<?= htmlspecialchars($fieldName) ?>" value="1" <?= $val === '1' || $val === 'true' || $val === 'on' ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                            <span class="ss-switch-label"><?= ($val === '1' || $val === 'true' || $val === 'on') ? 'Enabled' : 'Disabled' ?></span>
                                        </label>
                                    </div>
                                    <?php break;
                                default: ?>
                                    <div class="ss-form-group ss-float">
                                        <input type="<?= htmlspecialchars($type) ?>"
                                               name="<?= htmlspecialchars($fieldName) ?>"
                                               id="<?= htmlspecialchars($fieldId) ?>"
                                               value="<?= htmlspecialchars($val) ?>"
                                               placeholder=" ">
                                        <label for="<?= htmlspecialchars($fieldId) ?>"><?= htmlspecialchars($label) ?></label>
                                    </div>
                                    <?php break;
                            endswitch; ?>
                            <?php if (!empty($def['hint'])): ?>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);margin-top:-0.5rem;margin-bottom:0.5rem;"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($def['hint']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <!-- System Info Tab (read-only, no form fields) -->
            <div class="ss-tab-pane" data-tab="system" style="padding:1.5rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-code me-2"></i>PHP Version</span>
                            <span style="font-size:0.82rem;font-weight:700;color:var(--ss-primary);"><?= PHP_VERSION ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-database me-2"></i>MySQL Version</span>
                            <span style="font-size:0.82rem;font-weight:700;color:var(--ss-primary);">8.0+</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-server me-2"></i>Server Software</span>
                            <span style="font-size:0.82rem;font-weight:700;"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-memory me-2"></i>Memory Limit</span>
                            <span style="font-size:0.82rem;font-weight:700;"><?= htmlspecialchars(ini_get('memory_limit') ?: '—') ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-upload me-2"></i>Max Upload Size</span>
                            <span style="font-size:0.82rem;font-weight:700;"><?= htmlspecialchars(ini_get('upload_max_filesize') ?: '—') ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-clock me-2"></i>Timezone</span>
                            <span style="font-size:0.82rem;font-weight:700;"><?= date_default_timezone_get() ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-hdd me-2"></i>Disk Free Space</span>
                            <span style="font-size:0.82rem;font-weight:700;color:var(--ss-success);">
                                <?= function_exists('disk_free_space') ? round(disk_free_space('.') / 1073741824, 1) . ' GB' : 'N/A' ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-tag me-2"></i>App Version</span>
                            <span style="font-size:0.82rem;font-weight:700;color:var(--ss-primary);">v3.0.0</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div style="font-size:0.72rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Loaded PHP Extensions</div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (array_slice(get_loaded_extensions(), 0, 30) as $ext): ?>
                        <span class="ss-chip" style="font-size:0.7rem;"><?= htmlspecialchars($ext) ?></span>
                        <?php endforeach; ?>
                        <span class="ss-chip" style="font-size:0.7rem;color:var(--ss-text-3);">+<?= max(0, count(get_loaded_extensions()) - 30) ?> more</span>
                    </div>
                </div>
            </div>

            <!-- Sticky save bar -->
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--ss-border);background:var(--ss-surface-2);display:flex;align-items:center;gap:0.75rem;border-radius:0 0 12px 12px;">
                <div style="flex:1;">
                    <div style="font-size:0.78rem;color:var(--ss-text-3);">
                        <i class="fas fa-info-circle"></i>
                        Changes apply system-wide and may affect all users.
                    </div>
                </div>
                <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-ghost"><i class="fas fa-undo"></i> Reset</a>
                <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== DANGER ZONE ==================== -->
<div class="ss-card ss-animate-fade-up mt-4" style="border:1px solid var(--ss-danger);">
    <div class="ss-card-header">
        <h3 style="color:var(--ss-danger);"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
    </div>
    <div class="ss-card-body">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div style="flex:1;min-width:240px;">
                <div style="font-size:0.88rem;font-weight:600;">Clear Application Cache</div>
                <div style="font-size:0.78rem;color:var(--ss-text-2);">Removes cached routes, views, and config. May briefly slow down the site.</div>
            </div>
            <form method="POST" action="<?= URL::to('admin/settings/cache/clear') ?>" style="display:inline;">
                <?= $csrfField ?? '' ?>
                <button type="submit" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-broom"></i> Clear Cache</button>
            </form>
        </div>
        <hr style="border-color:var(--ss-border);margin:1rem 0;">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div style="flex:1;min-width:240px;">
                <div style="font-size:0.88rem;font-weight:600;">Reset All Settings to Defaults</div>
                <div style="font-size:0.78rem;color:var(--ss-text-2);">Restores every setting to its factory default. This action cannot be undone.</div>
            </div>
            <form method="POST" action="<?= URL::to('admin/settings/reset') ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to reset ALL settings to defaults? This cannot be undone.');">
                <?= $csrfField ?? '' ?>
                <button type="submit" class="ss-btn ss-btn-sm" style="background:var(--ss-danger);color:#fff;border:none;"><i class="fas fa-undo-alt"></i> Reset All</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Tab switching
    const tabs = document.querySelectorAll('.ss-tab[data-tab-target]');
    const panes = document.querySelectorAll('.ss-tab-pane[data-tab]');
    tabs.forEach(t => {
        t.addEventListener('click', function() {
            const target = this.getAttribute('data-tab-target');
            tabs.forEach(x => x.classList.remove('active'));
            panes.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            const pane = document.querySelector('.ss-tab-pane[data-tab="' + target + '"]');
            if (pane) pane.classList.add('active');
        });
    });

    // Update switch labels on toggle
    document.querySelectorAll('.ss-switch input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function() {
            const label = this.parentElement.querySelector('.ss-switch-label');
            if (label) label.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    });
})();
</script>
