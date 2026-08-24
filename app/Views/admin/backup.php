<?php
/**
 * Admin — Backup & Restore
 * Data: $systemInfo
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Backup & Restore';
$sys = $systemInfo ?? [];
$diskFreeGB = $sys['disk_free'] ? round($sys['disk_free'] / 1073741824, 1) : 0;
?>
<?= Component::pageHeader('Backup & Restore', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Backup</span>') ?>

<div class="row g-4">
    <!-- Backup Actions -->
    <div class="col-lg-8">
        <div class="ss-card mb-4">
            <div class="ss-card-header"><h3><i class="fas fa-database text-primary"></i> Database Backup</h3></div>
            <div class="ss-card-body">
                <p style="font-size:0.875rem;color:var(--ss-text-2);margin-bottom:1.5rem;">Create a full backup of your SkillSystem database. Backups include all tables, relationships, and data.</p>
                <div class="d-flex gap-2 flex-wrap mb-4">
                    <button class="ss-btn ss-btn-gradient" onclick="window.ssToast && ssToast.show('Backup started. You will be notified when it completes.', 'info')"><i class="fas fa-download"></i> Create Backup Now</button>
                    <button class="ss-btn ss-btn-light" onclick="window.ssToast && ssToast.show('Auto-backup schedule updated.', 'success')"><i class="fas fa-clock"></i> Schedule Auto-Backup</button>
                </div>

                <div style="font-size:0.78rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Backup History</div>
                <div class="d-flex flex-column gap-2">
                    <?php
                    $backups = [
                        ['name' => 'skillsystem_backup_2025-07-01.sql', 'size' => '2.4 MB', 'date' => 'Jul 1, 2025 6:00 AM', 'type' => 'auto'],
                        ['name' => 'skillsystem_backup_2025-06-30.sql', 'size' => '2.3 MB', 'date' => 'Jun 30, 2025 6:00 AM', 'type' => 'auto'],
                        ['name' => 'skillsystem_backup_2025-06-29.sql', 'size' => '2.3 MB', 'date' => 'Jun 29, 2025 6:00 AM', 'type' => 'auto'],
                        ['name' => 'skillsystem_backup_manual.sql', 'size' => '2.2 MB', 'date' => 'Jun 28, 2025 2:15 PM', 'type' => 'manual'],
                    ];
                    foreach ($backups as $b):
                    ?>
                    <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:40px;height:40px;border-radius:8px;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-file-archive"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($b['name']) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($b['date']) ?> · <?= htmlspecialchars($b['size']) ?></div>
                        </div>
                        <span class="ss-badge ss-badge-<?= $b['type'] === 'auto' ? 'info' : 'primary' ?>"><?= ucfirst($b['type']) ?></span>
                        <button class="ss-btn ss-btn-ghost ss-btn-sm" onclick="window.ssToast && ssToast.show('Downloading ' + '<?= htmlspecialchars($b['name']) ?>', 'info')"><i class="fas fa-download"></i></button>
                        <button class="ss-btn ss-btn-ghost ss-btn-sm" style="color:var(--ss-danger);" onclick="window.ssToast && ssToast.show('Backup deleted.', 'warning')"><i class="fas fa-trash"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Restore -->
        <div class="ss-card">
            <div class="ss-card-header"><h3><i class="fas fa-upload text-warning"></i> Restore Database</h3></div>
            <div class="ss-card-body">
                <div class="ss-alert ss-alert-warning" style="margin-bottom:1rem;">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-body"><strong>Warning:</strong> Restoring a backup will overwrite all current data. Make sure you have a recent backup before proceeding.</div>
                </div>
                <div class="ss-file-upload" onclick="this.querySelector('input').click()">
                    <input type="file" hidden accept=".sql,.zip" onchange="window.ssToast && ssToast.show('File selected. Click Restore to continue.', 'info')">
                    <div class="upload-icon"><i class="fas fa-upload"></i></div>
                    <div class="upload-text">Click to select a backup file</div>
                    <div class="upload-hint">.sql or .zip files up to <?= htmlspecialchars($sys['max_upload'] ?? '2M') ?></div>
                </div>
                <button class="ss-btn ss-btn-warning ss-btn-block mt-3" onclick="window.ssToast && ssToast.show('Restore process started.', 'warning')"><i class="fas fa-undo"></i> Restore Database</button>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="col-lg-4">
        <div class="ss-card mb-4">
            <div class="ss-card-header"><h3><i class="fas fa-cog text-primary"></i> Backup Settings</h3></div>
            <div class="ss-card-body">
                <div class="ss-form-group">
                    <label class="ss-form-label">Auto-Backup Frequency</label>
                    <select class="ss-select">
                        <option>Every 6 hours</option>
                        <option selected>Daily (6:00 AM)</option>
                        <option>Weekly (Sunday 6:00 AM)</option>
                        <option>Monthly (1st of month)</option>
                    </select>
                </div>
                <div class="ss-form-group">
                    <label class="ss-form-label">Keep Backups For</label>
                    <select class="ss-select">
                        <option>7 days</option>
                        <option selected>30 days</option>
                        <option>90 days</option>
                        <option>1 year</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                    <span style="font-size:0.82rem;font-weight:600;">Auto-Backup</span>
                    <label class="ss-switch"><input type="checkbox" checked><span class="slider"></span></label>
                </div>
                <div class="d-flex justify-content-between align-items-center p-2 mt-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                    <span style="font-size:0.82rem;font-weight:600;">Email Notification</span>
                    <label class="ss-switch"><input type="checkbox" checked><span class="slider"></span></label>
                </div>
                <button class="ss-btn ss-btn-gradient ss-btn-block mt-3" onclick="window.ssToast && ssToast.show('Settings saved.', 'success')"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </div>

        <div class="ss-card">
            <div class="ss-card-header"><h3><i class="fas fa-hdd text-primary"></i> Storage Info</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between"><span style="font-size:0.82rem;color:var(--ss-text-3);">Free Space</span><span style="font-size:0.82rem;font-weight:600;color:var(--ss-success);"><?= $diskFreeGB ?> GB</span></div>
                    <div class="d-flex justify-content-between"><span style="font-size:0.82rem;color:var(--ss-text-3);">Upload Limit</span><span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($sys['max_upload'] ?? '—') ?></span></div>
                    <div class="d-flex justify-content-between"><span style="font-size:0.82rem;color:var(--ss-text-3);">PHP Version</span><span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($sys['php_version'] ?? '—') ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
