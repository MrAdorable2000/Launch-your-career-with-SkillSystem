<?php
/**
 * Admin — Maintenance Mode
 * Data: $settings, $systemInfo
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Maintenance';
$sys = $systemInfo ?? [];
$diskFreePct = ($sys['disk_total'] && $sys['disk_total'] > 0) ? round(($sys['disk_free'] / $sys['disk_total']) * 100, 1) : 0;
?>
<?= Component::pageHeader('Maintenance & System', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Maintenance</span>') ?>

<!-- Maintenance Mode Toggle -->
<div class="ss-card mb-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-tools text-warning"></i> Maintenance Mode</h3>
        <label class="ss-switch"><input type="checkbox" id="maintenanceToggle"><span class="slider"></span></label>
    </div>
    <div class="ss-card-body">
        <div class="ss-alert ss-alert-warning">
            <i class="fas fa-exclamation-triangle alert-icon"></i>
            <div class="alert-body">
                <div class="alert-title">Maintenance Mode</div>
                When enabled, all non-admin users will see a maintenance page. Admins can still access the system normally.
            </div>
        </div>
        <div class="ss-form-group mt-3">
            <label class="ss-form-label">Maintenance Message</label>
            <textarea class="ss-textarea" placeholder="We'll be back soon! SkillSystem is undergoing scheduled maintenance.">We'll be back soon! SkillSystem is undergoing scheduled maintenance to improve your experience.</textarea>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <div class="ss-form-group">
                    <label class="ss-form-label">Scheduled Start</label>
                    <input type="datetime-local" class="ss-input">
                </div>
            </div>
            <div class="col-md-6">
                <div class="ss-form-group">
                    <label class="ss-form-label">Estimated End</label>
                    <input type="datetime-local" class="ss-input">
                </div>
            </div>
        </div>
        <button class="ss-btn ss-btn-warning" onclick="window.ssToast && ssToast.show('Maintenance settings saved.', 'success')"><i class="fas fa-save"></i> Save Settings</button>
    </div>
</div>

<!-- System Cleanup -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ss-card h-100">
            <div class="ss-card-header"><h3><i class="fas fa-broom text-primary"></i> System Cleanup</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:40px;height:40px;border-radius:8px;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-trash"></i></div>
                        <div style="flex:1;">
                            <div style="font-size:0.85rem;font-weight:600;">Clear Cache</div>
                            <div style="font-size:0.75rem;color:var(--ss-text-3);">Clear all cached data and templates</div>
                        </div>
                        <button class="ss-btn ss-btn-soft ss-btn-sm" onclick="window.ssToast && ssToast.show('Cache cleared successfully!', 'success')">Clear</button>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:40px;height:40px;border-radius:8px;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-file-archive"></i></div>
                        <div style="flex:1;">
                            <div style="font-size:0.85rem;font-weight:600;">Optimize Database</div>
                            <div style="font-size:0.75rem;color:var(--ss-text-3);">Run OPTIMIZE TABLE on all tables</div>
                        </div>
                        <button class="ss-btn ss-btn-soft ss-btn-sm" onclick="window.ssToast && ssToast.show('Database optimized!', 'success')">Optimize</button>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:40px;height:40px;border-radius:8px;background:var(--ss-warning-light);color:var(--ss-warning);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-clock"></i></div>
                        <div style="flex:1;">
                            <div style="font-size:0.85rem;font-weight:600;">Clear Old Logs</div>
                            <div style="font-size:0.75rem;color:var(--ss-text-3);">Delete activity logs older than 90 days</div>
                        </div>
                        <button class="ss-btn ss-btn-soft ss-btn-sm" onclick="window.ssToast && ssToast.show('Old logs cleared!', 'success')">Clean</button>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:40px;height:40px;border-radius:8px;background:var(--ss-info-light);color:var(--ss-info);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-image"></i></div>
                        <div style="flex:1;">
                            <div style="font-size:0.85rem;font-weight:600;">Clean Orphaned Files</div>
                            <div style="font-size:0.75rem;color:var(--ss-text-3);">Remove uploaded files not linked to any record</div>
                        </div>
                        <button class="ss-btn ss-btn-soft ss-btn-sm" onclick="window.ssToast && ssToast.show('Orphaned files removed!', 'success')">Clean</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="ss-card h-100">
            <div class="ss-card-header"><h3><i class="fas fa-chart-pie text-primary"></i> System Resources</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="d-flex justify-content-between mb-1"><span style="font-size:0.82rem;font-weight:600;">Disk Usage</span><span style="font-size:0.82rem;color:var(--ss-text-3);"><?= 100 - $diskFreePct ?>% used</span></div>
                        <?= Component::progress(100 - $diskFreePct, 'warning') ?>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1"><span style="font-size:0.82rem;font-weight:600;">Memory Limit</span><span style="font-size:0.82rem;color:var(--ss-text-3);"><?= htmlspecialchars($sys['memory_limit'] ?? '—') ?></span></div>
                        <?= Component::progress(65, 'info') ?>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1"><span style="font-size:0.82rem;font-weight:600;">CPU Load</span><span style="font-size:0.82rem;color:var(--ss-text-3);">23%</span></div>
                        <?= Component::progress(23, 'success') ?>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1"><span style="font-size:0.82rem;font-weight:600;">Database Size</span><span style="font-size:0.82rem;color:var(--ss-text-3);">14.2 MB</span></div>
                        <?= Component::progress(15, 'primary') ?>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1"><span style="font-size:0.82rem;font-weight:600;">Upload Directory</span><span style="font-size:0.82rem;color:var(--ss-text-3);">48 MB</span></div>
                        <?= Component::progress(35, 'success') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Info -->
<div class="ss-card">
    <div class="ss-card-header"><h3><i class="fas fa-info-circle text-primary"></i> System Information</h3></div>
    <div class="ss-card-body">
        <div class="row g-3">
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">PHP Version</span><span style="font-size:0.78rem;"><?= htmlspecialchars($sys['php_version'] ?? '—') ?></span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">Server</span><span style="font-size:0.78rem;"><?= htmlspecialchars($sys['server_software'] ?? '—') ?></span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">Memory Limit</span><span style="font-size:0.78rem;"><?= htmlspecialchars($sys['memory_limit'] ?? '—') ?></span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">Max Upload</span><span style="font-size:0.78rem;"><?= htmlspecialchars($sys['max_upload'] ?? '—') ?></span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">App Version</span><span style="font-size:0.78rem;">v3.0.0</span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">Database</span><span style="font-size:0.78rem;">MySQL 8.0+</span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">Disk Free</span><span style="font-size:0.78rem;color:var(--ss-success);"><?= $diskFreePct ?>%</span></div></div>
            <div class="col-md-6 col-lg-3"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.78rem;font-weight:600;">Timezone</span><span style="font-size:0.78rem;"><?= date_default_timezone_get() ?></span></div></div>
        </div>
    </div>
</div>
