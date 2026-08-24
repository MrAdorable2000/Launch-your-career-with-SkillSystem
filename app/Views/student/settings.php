<?php
/**
 * Settings — Premium redesign (v3)
 *
 * Data passed from StudentController::settings(): none (uses Session helpers).
 *
 * Form posts to student/settings/update with fields:
 *   current_password, new_password, new_password_confirmation
 *
 * Notification preferences + privacy toggles are wired up to localstorage / future endpoints.
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
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Settings</span>',
    '<span class="ss-badge ss-badge-success ss-badge-lg"><i class="fas fa-shield-alt"></i> Secured</span>'
) ?>

<div data-tabs>
    <!-- Tabs -->
    <div class="ss-tabs">
        <button class="ss-tab active" data-tab="#set-account"><i class="fas fa-user"></i> Account</button>
        <button class="ss-tab" data-tab="#set-password"><i class="fas fa-key"></i> Password</button>
        <button class="ss-tab" data-tab="#set-notifications"><i class="fas fa-bell"></i> Notifications</button>
        <button class="ss-tab" data-tab="#set-privacy"><i class="fas fa-lock"></i> Privacy</button>
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
                                        <div style="width:80px;height:80px;border-radius:50%;background:var(--ss-primary-light);color:var(--ss-primary);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;border:3px solid var(--ss-border);"><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></div>
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
                                <a href="<?= URL::to('student/dashboard') ?>" class="ss-btn ss-btn-light">Cancel</a>
                            </div>
                        </form>

                        <hr class="my-4">
                        <?= Component::alert(
                            'Visit your <a href="' . URL::to('student/profile') . '" class="fw-semibold">profile page</a> to edit bio, skills, education, experience, and portfolio.',
                            'info',
                            'More profile details?'
                        ) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ss-card ss-animate-fade-up mb-4">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-shield-alt text-success"></i> Account Status</h3>
                    </div>
                    <div class="ss-card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="ss-badge ss-badge-success ss-badge-lg"><i class="fas fa-check-circle"></i> Active</span>
                            <span class="ss-badge ss-badge-soft ss-badge-lg"><i class="fas fa-shield-alt"></i> Verified</span>
                        </div>
                        <div style="font-size:0.82rem;color:var(--ss-text-3);line-height:1.7;">
                            <div><strong>Member since:</strong> <?= htmlspecialchars(date('F Y')) ?></div>
                            <div><strong>Last login:</strong> <?= htmlspecialchars(date('M j, Y g:i A')) ?></div>
                        </div>
                    </div>
                </div>

                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-bolt text-warning"></i> Quick Actions</h3>
                    </div>
                    <div class="ss-card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-soft"><i class="fas fa-pen"></i> Edit Profile</a>
                            <a href="<?= URL::to('student/resume') ?>" class="ss-btn ss-btn-light"><i class="fas fa-file-alt"></i> Manage Resumes</a>
                            <a href="<?= URL::to('logout') ?>" class="ss-btn ss-btn-light" style="color:var(--ss-danger);"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
                        </div>
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
                            ['icon' => 'fa-check',      'color' => 'success', 'title' => 'Email verified',     'desc' => 'Your email is confirmed'],
                            ['icon' => 'fa-exclamation','color' => 'warning', 'title' => '2FA not enabled',    'desc' => 'Add an extra layer of security'],
                            ['icon' => 'fa-key',         'color' => 'success', 'title' => 'Strong password',    'desc' => 'Last changed 3 months ago'],
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
                        ['icon' => 'fa-briefcase',         'title' => 'New job matches',          'desc' => "When a job matches 70%+ of your skills", 'email' => true,  'push' => true,  'app' => true],
                        ['icon' => 'fa-check-circle',       'title' => 'Application status update','desc' => "When an employer reviews or updates your application", 'email' => true,  'push' => false, 'app' => true],
                        ['icon' => 'fa-envelope',           'title' => 'New messages',             'desc' => "When you receive a message from an employer or mentor", 'email' => true,  'push' => true,  'app' => true],
                        ['icon' => 'fa-calendar',           'title' => 'Event reminders',          'desc' => "Reminders for upcoming events and interviews", 'email' => true,  'push' => true,  'app' => true],
                        ['icon' => 'fa-medal',              'title' => 'Badges & achievements',    'desc' => "When you unlock a new badge or milestone", 'email' => false, 'push' => false, 'app' => true],
                        ['icon' => 'fa-graduation-cap',     'title' => 'Internship deadlines',     'desc' => "When an internship you saved is closing soon", 'email' => true,  'push' => true,  'app' => false],
                        ['icon' => 'fa-newspaper',          'title' => 'Weekly digest',            'desc' => "A summary of new jobs and activity every Monday", 'email' => true,  'push' => false, 'app' => false],
                        ['icon' => 'fa-bullhorn',           'title' => 'Product updates',          'desc' => "News about new features and improvements", 'email' => false, 'push' => false, 'app' => true],
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

    <!-- ============== PRIVACY ============== -->
    <div class="ss-tab-pane" id="set-privacy">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-lock text-primary"></i> Privacy Controls</h3>
                    </div>
                    <div class="ss-card-body">
                        <form method="POST" action="<?= URL::to('student/settings/update') ?>">
                            <?= $csrfField ?? '' ?>
                            <?php
                            $privacyRows = [
                                ['name' => 'profile_visible',      'icon' => 'fa-eye',           'title' => 'Public profile',          'desc' => "Allow employers to discover and view your profile", 'checked' => true],
                                ['name' => 'show_resume',          'icon' => 'fa-file-alt',      'title' => 'Resume visibility',       'desc' => "Include your default resume in employer searches", 'checked' => true],
                                ['name' => 'show_skills',          'icon' => 'fa-code',          'title' => 'Skills showcase',         'desc' => "Display your skills on your public profile", 'checked' => true],
                                ['name' => 'show_portfolio',       'icon' => 'fa-folder-open',   'title' => 'Portfolio public access', 'desc' => "Let anyone with the link view your portfolio", 'checked' => true],
                                ['name' => 'contact_employers',    'icon' => 'fa-envelope',      'title' => 'Direct contact',          'desc' => "Allow verified employers to message you directly", 'checked' => true],
                                ['name' => 'analytics_tracking',   'icon' => 'fa-chart-line',    'title' => 'Anonymous analytics',     'desc' => "Help us improve by sharing anonymous usage data", 'checked' => true],
                            ];
                            foreach ($privacyRows as $row):
                            ?>
                            <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                                <span class="ss-avatar ss-avatar-sm bg-soft-info"><i class="fas <?= $row['icon'] ?>"></i></span>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="fw-semibold" style="font-size:0.88rem;"><?= htmlspecialchars($row['title']) ?></div>
                                    <div style="color:var(--ss-text-3);font-size:0.75rem;"><?= htmlspecialchars($row['desc']) ?></div>
                                </div>
                                <label class="ss-switch">
                                    <input type="checkbox" name="<?= htmlspecialchars($row['name']) ?>" value="1" <?= $row['checked'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <button type="submit" class="ss-btn ss-btn-gradient mt-4"><i class="fas fa-save"></i> Save Privacy Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Danger zone -->
            <div class="col-lg-5">
                <div class="ss-card ss-animate-fade-up" style="border-color:rgba(var(--ss-danger-rgb),0.3);">
                    <div class="ss-card-header" style="background:var(--ss-danger-light);border-bottom-color:rgba(var(--ss-danger-rgb),0.2);">
                        <h3 style="color:var(--ss-danger);"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                    </div>
                    <div class="ss-card-body">
                        <?= Component::alert(
                            'All your applications, messages, portfolio and badges will be erased. This cannot be undone.',
                            'danger',
                            'Account deletion is permanent'
                        ) ?>

                        <div class="ss-form-group">
                            <label class="ss-form-label">Type <strong>DELETE</strong> to confirm</label>
                            <input type="text" class="ss-input" id="delete-confirm" placeholder="Type DELETE" oninput="document.getElementById('delete-btn').disabled = (this.value !== 'DELETE');">
                        </div>

                        <form method="POST" action="<?= URL::to('student/account/delete') ?>" onsubmit="return confirm('This will permanently delete your account. Are you absolutely sure?');">
                            <?= $csrfField ?? '' ?>
                            <button type="submit" class="ss-btn ss-btn-danger ss-btn-block" id="delete-btn" disabled><i class="fas fa-trash"></i> Delete My Account</button>
                        </form>

                        <div class="divider-h my-3"></div>

                        <div style="font-size:0.78rem;color:var(--ss-text-3);">
                            <i class="fas fa-info-circle"></i> Prefer to take a break? You can <a href="<?= URL::to('logout') ?>" class="fw-semibold">sign out</a> and come back anytime — your data will be waiting.
                        </div>
                    </div>
                </div>
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
