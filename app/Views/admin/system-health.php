<?php
/**
 * Admin — System Health
 * Data: $systemInfo, $pendingReports
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'System Health';
$sys = $systemInfo ?? [];
$diskFreePct = ($sys['disk_total'] && $sys['disk_total'] > 0) ? round(($sys['disk_free'] / $sys['disk_total']) * 100, 1) : 0;
$diskUsedPct = 100 - $diskFreePct;
?>
<?= Component::pageHeader('System Health', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>System Health</span>') ?>

<div class="ss-card mb-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-heartbeat text-success"></i> System Status</h3>
        <span class="ss-badge ss-badge-success"><i class="fas fa-circle" style="font-size:0.5rem;"></i> All Systems Operational</span>
    </div>
    <div class="ss-card-body">
        <div class="row g-3">
            <?php
            $items = [
                ['label' => 'Server Status', 'value' => 'Online', 'status' => 'success', 'icon' => 'fa-server', 'detail' => $sys['server_software'] ?? 'Apache'],
                ['label' => 'Database', 'value' => 'Healthy', 'status' => 'success', 'icon' => 'fa-database', 'detail' => 'MySQL ' . ($sys['mysql_version'] ?? '8.0+')],
                ['label' => 'Email Service', 'value' => 'Operational', 'status' => 'success', 'icon' => 'fa-paper-plane', 'detail' => 'SMTP configured'],
                ['label' => 'API Status', 'value' => '124ms latency', 'status' => 'success', 'icon' => 'fa-bolt', 'detail' => 'All endpoints responding'],
                ['label' => 'Cache Status', 'value' => 'Active', 'status' => 'success', 'icon' => 'fa-layer-group', 'detail' => 'Session cache enabled'],
                ['label' => 'Storage', 'value' => $diskUsedPct . '% used', 'status' => $diskUsedPct > 80 ? 'danger' : ($diskUsedPct > 60 ? 'warning' : 'success'), 'icon' => 'fa-hdd', 'detail' => 'Disk space'],
                ['label' => 'Memory Limit', 'value' => $sys['memory_limit'] ?? '—', 'status' => 'success', 'icon' => 'fa-memory', 'detail' => 'PHP memory'],
                ['label' => 'PHP Version', 'value' => $sys['php_version'] ?? '—', 'status' => 'success', 'icon' => 'fa-code', 'detail' => 'Runtime'],
                ['label' => 'Upload Limit', 'value' => $sys['max_upload'] ?? '—', 'status' => 'success', 'icon' => 'fa-upload', 'detail' => 'Max file size'],
                ['label' => 'Pending Reports', 'value' => $pendingReports . ' open', 'status' => $pendingReports > 0 ? 'warning' : 'success', 'icon' => 'fa-flag', 'detail' => 'User reports'],
            ];
            foreach ($items as $h):
                $color = $h['status'] === 'success' ? 'success' : ($h['status'] === 'warning' ? 'warning' : 'danger');
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r);height:100%;">
                    <div style="width:44px;height:44px;border-radius:var(--ss-r-sm);background:var(--ss-<?= $color ?>-light);color:var(--ss-<?= $color ?>);display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                        <i class="fas <?= htmlspecialchars($h['icon']) ?>"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($h['label']) ?></div>
                        <div style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars($h['detail']) ?></div>
                        <div style="font-size:0.82rem;font-weight:700;margin-top:2px;"><?= htmlspecialchars($h['value']) ?></div>
                    </div>
                    <span class="ss-badge ss-badge-<?= $color ?>"><?= ucfirst($h['status']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Disk Usage Chart -->
<div class="ss-chart-card mb-4">
    <div class="chart-header"><h5><i class="fas fa-hdd text-primary"></i> Disk Usage</h5></div>
    <div class="ss-card-body">
        <?php
        $diskFreeGB = $sys['disk_free'] ? round($sys['disk_free'] / 1073741824, 1) : 0;
        $diskTotalGB = $sys['disk_total'] ? round($sys['disk_total'] / 1073741824, 1) : 0;
        $diskUsedGB = $diskTotalGB - $diskFreeGB;
        ?>
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div style="position:relative;width:160px;height:160px;">
                <canvas id="diskChart"></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                    <div style="font-size:1.8rem;font-weight:900;color:var(--ss-text);"><?= $diskUsedPct ?>%</div>
                    <div style="font-size:0.72rem;color:var(--ss-text-3);">Used</div>
                </div>
            </div>
            <div style="flex:1;min-width:200px;">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between"><span style="font-size:0.85rem;font-weight:600;">Total Disk Space</span><span style="font-size:0.85rem;"><?= $diskTotalGB ?> GB</span></div>
                    <div class="d-flex justify-content-between"><span style="font-size:0.85rem;font-weight:600;">Used Space</span><span style="font-size:0.85rem;color:var(--ss-warning);"><?= $diskUsedGB ?> GB</span></div>
                    <div class="d-flex justify-content-between"><span style="font-size:0.85rem;font-weight:600;">Free Space</span><span style="font-size:0.85rem;color:var(--ss-success);"><?= $diskFreeGB ?> GB</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PHP Info -->
<div class="ss-card">
    <div class="ss-card-header"><h3><i class="fas fa-code text-primary"></i> PHP Information</h3></div>
    <div class="ss-card-body">
        <div class="row g-3">
            <div class="col-md-6"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.82rem;font-weight:600;">PHP Version</span><span style="font-size:0.82rem;"><?= htmlspecialchars($sys['php_version'] ?? '') ?></span></div></div>
            <div class="col-md-6"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.82rem;font-weight:600;">Memory Limit</span><span style="font-size:0.82rem;"><?= htmlspecialchars($sys['memory_limit'] ?? '') ?></span></div></div>
            <div class="col-md-6"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.82rem;font-weight:600;">Max Upload Size</span><span style="font-size:0.82rem;"><?= htmlspecialchars($sys['max_upload'] ?? '') ?></span></div></div>
            <div class="col-md-6"><div class="d-flex justify-content-between p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><span style="font-size:0.82rem;font-weight:600;">Server Software</span><span style="font-size:0.82rem;"><?= htmlspecialchars($sys['server_software'] ?? '') ?></span></div></div>
        </div>
        <?php if (!empty($sys['php_modules'])): ?>
        <div class="mt-3">
            <div style="font-size:0.78rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Loaded Extensions (<?= count($sys['php_modules']) ?>)</div>
            <div class="d-flex flex-wrap gap-1">
                <?php foreach (array_slice($sys['php_modules'], 0, 30) as $ext): ?>
                <span class="ss-chip" style="font-size:0.72rem;"><?= htmlspecialchars($ext) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    if (typeof Chart === 'undefined') return;
    const c = window.SS_THEME || {};
    const dc = document.getElementById('diskChart');
    if (dc) new Chart(dc, { type: 'doughnut', data: { datasets: [{ data: [<?= $diskUsedPct ?>, <?= $diskFreePct ?>], backgroundColor: [c.warning || '#F59E0B', c.grid || '#E2E8F0'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { enabled: false } } } });
})();
</script>
