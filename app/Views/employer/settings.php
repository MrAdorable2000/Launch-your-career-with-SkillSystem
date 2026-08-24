<?php
/**
 * Employer — Settings (premium redesign v3)
 *
 * Data: settings() controller passes no data, but the layout exposes:
 *   $userName, $userEmail, $userRole, $csrfField
 *
 * Form actions:
 *   - Password change: POST /student/settings/update (existing route)
 *     Controller reads: current_password, new_password, new_password_confirmation, _token
 *
 * Tabs: Account, Password, Notifications.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Settings';

$userEmail = Session::get('userEmail');
$userRole  = Session::userRole();
$userName  = Session::userName();
?>
<?= Component::pageHeader(
    'Account Settings',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <span>Settings</span>',
    '<span class="ss-badge ss-badge-success ss-badge-lg"><i class="fas fa-shield-alt"></i> Secured</span>'
) ?>

<div data-tabs>
    <!-- ============== TABS ============== -->
    <div class="ss-tabs">
        <button class="ss-tab active" data-tab="#set-account"><i class="fas fa-user"></i> Account</button>
        <button class="ss-tab" data-tab="#set-password"><i class="fas fa-key"></i> Password</button>
        <button class="ss-tab" data-tab="#set-notifications"><i class="fas fa-bell"></i> Notifications</button>
    </div>

    <!-- ============== ACCOUNT ============== -->
    <div class="ss-tab-pane active" id="set-account">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-user text-primary"></i> Account Information</h3>
                    </div>
                    <div class="ss-card-body">
                        <form method="POST" action="<?= URL::to('account/update') ?>" enctype="multipart/form-data" data-validate>
                            <?= $csrfField ?? '' ?>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div style="position:relative;">
                                    <?php $avatar = Session::get('userAvatar'); ?>
                                    <?php $avatarUrl = (!empty($avatar)) ? URL::asset($avatar) : ''; ?>
                                    <?php if (!empty($avatarUrl) && file_exists(ROOT_PATH . '/public/assets/' . $avatar)): ?>
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--ss-border);">
                                    <?php else: ?>
                                        <div style="width:80px;height:80px;border-radius:50%;background:var(--ss-success-light);color:var(--ss-success);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;border:3px solid var(--ss-border);"><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="ss-btn ss-btn-soft ss-btn-sm" style="cursor:pointer;">
                                        <i class="fas fa-camera"></i> Change Photo
                                        <input type="file" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp" data-file-preview="#avatar-preview" style="display:none;">
                                    </label>
                                    <div style="font-size:0.75rem;color:var(--ss-text-3);margin-top:4px;">JPG, PNG, GIF or WebP. Max 2MB.</div>
                                    <div id="avatar-preview" style="display:none;margin-top:8px;"><img style="width:60px;height:60px;border-radius:50%;object-fit:cover;"></div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="ss-form-group ss-float">
                                        <input type="text" name="first_name" id="acctFirst" placeholder=" " required value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                                        <label for="acctFirst">First name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="ss-form-group ss-float">
                                        <input type="text" name="last_name" id="acctLast" placeholder=" " required value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                                        <label for="acctLast">Last name *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="ss-form-group ss-float mb-3">
                                <input type="email" name="email" id="acctEmail" placeholder=" " required data-validate="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                <label for="acctEmail">Email address *</label>
                            </div>

                            <div class="ss-form-group ss-float mb-3">
                                <input type="tel" name="phone" id="acctPhone" placeholder=" " value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                <label for="acctPhone">Phone number</label>
                            </div>

                            <div class="ss-form-group ss-float mb-3">
                                <input type="text" class="ss-input" value="<?= htmlspecialchars(ucfirst($userRole ?? '')) ?>" disabled>
                                <label>Account type (cannot be changed)</label>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Changes</button>
                                <a href="<?= URL::to('employer/dashboard') ?>" class="ss-btn ss-btn-light">Cancel</a>
                            </div>
                        </form>

                        <hr class="my-4">
                        <?= Component::alert(
                            'Visit your <a href="' . URL::to('employer/company') . '" class="fw-semibold">company profile page</a> to update company name, logo, industry, website, and description.',
                            'info',
                            'Company details?'
                        ) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-shield-alt text-primary"></i> Security Status</h3>
                    </div>
                    <div class="ss-card-body">
                        <?php
                        $security = [
                            ['icon' => 'fa-check',       'color' => 'success', 'title' => 'Email verified',  'desc' => 'Your email is confirmed'],
                            ['icon' => 'fa-exclamation', 'color' => 'warning', 'title' => '2FA not enabled', 'desc' => 'Add an extra layer of security'],
                            ['icon' => 'fa-key',         'color' => 'success', 'title' => 'Strong password', 'desc' => 'Last changed 3 months ago'],
                        ];
                        foreach ($security as $s):
                        ?>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="ss-avatar ss-avatar-sm bg-soft-<?= $s['color'] ?>"><i class="fas <?= $s['icon'] ?>"></i></span>
                            <div>
                                <div class="fw-semibold" style="font-size:0.85rem;"><?= htmlspecialchars($s['title']) ?></div>
                                <div style="color:var(--ss-text-3);font-size:0.72rem;"><?= htmlspecialchars($s['desc']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <button class="ss-btn ss-btn-soft ss-btn-block" disabled title="Coming soon"><i class="fas fa-mobile-alt"></i> Enable Two-Factor Auth</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============== PASSWORD ============== -->
    <div class="ss-tab-pane" id="set-password">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-key text-primary"></i> Change Password</h3>
                    </div>
                    <div class="ss-card-body">
                        <form method="POST" action="<?= URL::to('student/settings/update') ?>" data-validate>
                            <?= $csrfField ?? '' ?>
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="current_password">Current password <span class="req">*</span></label>
                                <div class="ss-pw-wrap">
                                    <input type="password" name="current_password" id="current_password" class="ss-input" placeholder="Enter your current password" required>
                                    <button type="button" class="ss-pw-toggle" onclick="ssTogglePw(this)" aria-label="Show password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="new_password">New password <span class="req">*</span></label>
                                <div class="ss-pw-wrap">
                                    <input type="password" name="new_password" id="new_password" class="ss-input" placeholder="Enter at least 8 characters" data-password-strength="#pw-strength-target" data-min="8" required>
                                    <button type="button" class="ss-pw-toggle" onclick="ssTogglePw(this)" aria-label="Show password"><i class="fas fa-eye"></i></button>
                                </div>
                                <div id="pw-strength-target"></div>
                            </div>
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="new_password_confirmation">Confirm new password <span class="req">*</span></label>
                                <div class="ss-pw-wrap">
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="ss-input" placeholder="Re-enter your new password" data-match="new_password" required>
                                    <button type="button" class="ss-pw-toggle" onclick="ssTogglePw(this)" aria-label="Show password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-key"></i> Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-lightbulb text-primary"></i> Password Best Practices</h3>
                    </div>
                    <div class="ss-card-body" style="font-size:0.875rem;">
                        <?php
                        $practices = [
                            ['icon' => 'fa-check-circle', 'color' => 'success', 'text' => 'Use at least <strong>12 characters</strong> mixing letters, numbers and symbols.'],
                            ['icon' => 'fa-check-circle', 'color' => 'success', 'text' => 'Avoid reusing passwords from other sites.'],
                            ['icon' => 'fa-check-circle', 'color' => 'success', 'text' => 'Consider a password manager like <strong>1Password</strong> or <strong>Bitwarden</strong>.'],
                            ['icon' => 'fa-times-circle', 'color' => 'danger',  'text' => 'Never share your password — our team will never ask for it.'],
                        ];
                        foreach ($practices as $p):
                        ?>
                        <div class="d-flex gap-2 mb-3">
                            <i class="fas <?= $p['icon'] ?> text-<?= $p['color'] ?> mt-1"></i>
                            <div><?= $p['text'] ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============== NOTIFICATIONS ============== -->
    <div class="ss-tab-pane" id="set-notifications">
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-bell text-primary"></i> Notification Preferences</h3>
                <?= Component::badge('4 channels', 'soft') ?>
            </div>
            <div class="ss-card-body">
                <form method="POST" action="<?= URL::to('student/settings/update') ?>" id="notif-form">
                    <?= $csrfField ?? '' ?>

                    <div class="d-flex align-items-center gap-2 mb-3" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;color:var(--ss-text-3);">
                        <span class="flex-grow-1">Event</span>
                        <span style="width:60px;text-align:center;">Email</span>
                        <span style="width:60px;text-align:center;">Push</span>
                        <span style="width:60px;text-align:center;">In-app</span>
                    </div>

                    <?php
                    $notifRows = [
                        ['icon' => 'fa-paper-plane',  'title' => 'New job applications',       'desc' => "When a candidate applies to one of your jobs or internships",         'email' => true,  'push' => true,  'app' => true],
                        ['icon' => 'fa-user-check',   'title' => 'Application status changes', 'desc' => "When you mark a candidate as shortlisted, interview or offered",     'email' => false, 'push' => false, 'app' => true],
                        ['icon' => 'fa-envelope',     'title' => 'Candidate messages',         'desc' => "When a candidate sends you a message through the platform",          'email' => true,  'push' => true,  'app' => true],
                        ['icon' => 'fa-calendar',     'title' => 'Interview reminders',        'desc' => "Reminders for upcoming interviews with candidates",                  'email' => true,  'push' => true,  'app' => true],
                        ['icon' => 'fa-clock',        'title' => 'Job posting expiring',       'desc' => "When one of your job postings is about to reach its deadline",       'email' => true,  'push' => false, 'app' => true],
                        ['icon' => 'fa-bullhorn',     'title' => 'Product updates',            'desc' => "News about new features and improvements to the hiring platform",   'email' => false, 'push' => false, 'app' => true],
                        ['icon' => 'fa-chart-line',   'title' => 'Weekly hiring digest',       'desc' => "A summary of your jobs, applications and pipeline every Monday",    'email' => true,  'push' => false, 'app' => false],
                        ['icon' => 'fa-shield-alt',   'title' => 'Security alerts',            'desc' => "Critical alerts about your account safety and login activity",       'email' => true,  'push' => true,  'app' => true],
                    ];
                    foreach ($notifRows as $idx => $row):
                    ?>
                    <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                        <span class="ss-avatar ss-avatar-sm bg-soft-primary"><i class="fas <?= $row['icon'] ?>"></i></span>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.88rem;"><?= htmlspecialchars($row['title']) ?></div>
                            <div style="color:var(--ss-text-3);font-size:0.75rem;"><?= htmlspecialchars($row['desc']) ?></div>
                        </div>
                        <div style="width:60px;text-align:center;">
                            <label class="ss-switch">
                                <input type="checkbox" name="notif_email_<?= $idx ?>" value="1" <?= $row['email'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div style="width:60px;text-align:center;">
                            <label class="ss-switch">
                                <input type="checkbox" name="notif_push_<?= $idx ?>" value="1" <?= $row['push'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div style="width:60px;text-align:center;">
                            <label class="ss-switch">
                                <input type="checkbox" name="notif_app_<?= $idx ?>" value="1" <?= $row['app'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Preferences</button>
                        <button type="reset" class="ss-btn ss-btn-light">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility for the password fields
function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
