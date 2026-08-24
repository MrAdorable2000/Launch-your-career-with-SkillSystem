<?php
/**
 * Admin — Email & SMS Settings
 * Data: $settings
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Email & SMS';
?>
<?= Component::pageHeader('Email & SMS Settings', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Email & SMS</span>') ?>

<div class="row g-4">
    <!-- Email Settings -->
    <div class="col-lg-6">
        <div class="ss-card mb-4">
            <div class="ss-card-header">
                <h3><i class="fas fa-envelope text-primary"></i> Email Configuration</h3>
                <span class="ss-badge ss-badge-success"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active</span>
            </div>
            <div class="ss-card-body">
                <form method="POST" action="<?= URL::to('admin/settings/update') ?>">
                    <?= $csrfField ?? '' ?>
                    <div class="ss-form-group">
                        <label class="ss-form-label">SMTP Host</label>
                        <input type="text" class="ss-input" name="setting_smtp_host" value="smtp.gmail.com" placeholder="smtp.example.com">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">SMTP Port</label>
                                <input type="number" class="ss-input" name="setting_smtp_port" value="587">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Encryption</label>
                                <select class="ss-select" name="setting_smtp_encryption">
                                    <option value="tls" selected>TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">SMTP Username</label>
                        <input type="text" class="ss-input" name="setting_smtp_username" value="noreply@skillsystem.rw">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">SMTP Password</label>
                        <input type="password" class="ss-input" name="setting_smtp_password" placeholder="••••••••">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">From Email</label>
                                <input type="email" class="ss-input" name="setting_mail_from_address" value="noreply@skillsystem.rw">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">From Name</label>
                                <input type="text" class="ss-input" name="setting_mail_from_name" value="SkillSystem">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save</button>
                        <button type="button" class="ss-btn ss-btn-light" onclick="window.ssToast && ssToast.show('Test email sent!', 'success')"><i class="fas fa-paper-plane"></i> Send Test</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Templates -->
        <div class="ss-card">
            <div class="ss-card-header"><h3><i class="fas fa-file-alt text-primary"></i> Email Templates</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <?php
                    $templates = [
                        ['name' => 'Welcome Email', 'desc' => 'Sent to new users on registration', 'icon' => 'fa-handshake', 'color' => 'success'],
                        ['name' => 'Password Reset', 'desc' => 'Sent when user requests password reset', 'icon' => 'fa-key', 'color' => 'warning'],
                        ['name' => 'Job Application', 'desc' => 'Sent when a student applies for a job', 'icon' => 'fa-file-alt', 'color' => 'primary'],
                        ['name' => 'Interview Scheduled', 'desc' => 'Sent when an interview is booked', 'icon' => 'fa-calendar', 'color' => 'info'],
                        ['name' => 'Job Offer', 'desc' => 'Sent when an employer offers a position', 'icon' => 'fa-handshake', 'color' => 'success'],
                        ['name' => 'Certificate Issued', 'desc' => 'Sent when a certificate is verified', 'icon' => 'fa-certificate', 'color' => 'warning'],
                    ];
                    foreach ($templates as $t):
                    ?>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-<?= $t['color'] ?>-light);color:var(--ss-<?= $t['color'] ?>);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas <?= $t['icon'] ?>"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($t['name']) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($t['desc']) ?></div>
                        </div>
                        <button class="ss-btn ss-btn-ghost ss-btn-sm" onclick="window.ssToast && ssToast.show('Template editor opened.', 'info')"><i class="fas fa-edit"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Settings -->
    <div class="col-lg-6">
        <div class="ss-card mb-4">
            <div class="ss-card-header">
                <h3><i class="fas fa-sms text-primary"></i> SMS Configuration</h3>
                <label class="ss-switch"><input type="checkbox"><span class="slider"></span></label>
            </div>
            <div class="ss-card-body">
                <form onsubmit="event.preventDefault(); window.ssToast && ssToast.show('SMS settings saved.', 'success');">
                    <div class="ss-form-group">
                        <label class="ss-form-label">SMS Provider</label>
                        <select class="ss-select">
                            <option value="twilio">Twilio</option>
                            <option value="africa">Africa's Talking</option>
                            <option value="nexmo">Vonage (Nexmo)</option>
                            <option value="custom">Custom API</option>
                        </select>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">API Key / Account SID</label>
                        <input type="text" class="ss-input" placeholder="ACxxxxxxxxxxxxxxxxxxxx">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Auth Token</label>
                        <input type="password" class="ss-input" placeholder="••••••••">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Sender ID / From Number</label>
                        <input type="text" class="ss-input" placeholder="SkillSystem" value="SkillSystem">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Default Country Code</label>
                        <input type="text" class="ss-input" placeholder="+250" value="+250">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save</button>
                        <button type="button" class="ss-btn ss-btn-light" onclick="window.ssToast && ssToast.show('Test SMS sent!', 'success')"><i class="fas fa-paper-plane"></i> Send Test</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SMS Templates -->
        <div class="ss-card">
            <div class="ss-card-header"><h3><i class="fas fa-comment-dots text-primary"></i> SMS Templates</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <?php
                    $smsTemplates = [
                        ['name' => 'OTP / Verification', 'desc' => 'Phone verification code', 'icon' => 'fa-shield-alt', 'color' => 'success'],
                        ['name' => 'Interview Reminder', 'desc' => 'Sent 1 hour before interview', 'icon' => 'fa-calendar', 'color' => 'info'],
                        ['name' => 'Application Status', 'desc' => 'Application status update', 'icon' => 'fa-bell', 'color' => 'primary'],
                        ['name' => 'Event Reminder', 'desc' => 'Event starting soon', 'icon' => 'fa-calendar-alt', 'color' => 'warning'],
                    ];
                    foreach ($smsTemplates as $t):
                    ?>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-<?= $t['color'] ?>-light);color:var(--ss-<?= $t['color'] ?>);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas <?= $t['icon'] ?>"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($t['name']) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($t['desc']) ?></div>
                        </div>
                        <button class="ss-btn ss-btn-ghost ss-btn-sm" onclick="window.ssToast && ssToast.show('Template editor opened.', 'info')"><i class="fas fa-edit"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Preferences -->
<div class="ss-card mt-4">
    <div class="ss-card-header"><h3><i class="fas fa-bell text-primary"></i> Notification Preferences</h3></div>
    <div class="ss-card-body">
        <div class="table-responsive">
            <table class="ss-table">
                <thead><tr><th>Event</th><th>Email</th><th>SMS</th><th>In-App</th></tr></thead>
                <tbody>
                    <?php
                    $events = [
                        'New User Registration', 'New Job Application', 'Interview Scheduled',
                        'Job Offer', 'Certificate Verified', 'New Forum Post', 'Payment Received',
                        'Account Suspended', 'System Maintenance',
                    ];
                    foreach ($events as $ev):
                    ?>
                    <tr>
                        <td style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($ev) ?></td>
                        <td><label class="ss-switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                        <td><label class="ss-switch"><input type="checkbox"><span class="slider"></span></label></td>
                        <td><label class="ss-switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="ss-btn ss-btn-gradient mt-3" onclick="window.ssToast && ssToast.show('Preferences saved.', 'success')"><i class="fas fa-save"></i> Save Preferences</button>
    </div>
</div>
