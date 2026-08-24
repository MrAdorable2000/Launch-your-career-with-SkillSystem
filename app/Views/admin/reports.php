<?php
/**
 * Admin — Reports
 * Data: $stats, $roleDist, $topUniversities, $topCompanies, $deptData, $employmentRate
 */
use App\Helpers\URL;
use App\Helpers\Component;
use App\Helpers\Theme;
$chartColors = Theme::chartColors();
$pageTitle = 'Reports';
$s = $stats ?? [];
$deptLabels = []; $deptData2 = [];
foreach ($deptData ?? [] as $d) { $deptLabels[] = $d['department']; $deptData2[] = (int)$d['count']; }
$rdLabels = []; $rdData = [];
foreach ($roleDist ?? [] as $r) { $rdLabels[] = $r['role_name'] ?? 'Unknown'; $rdData[] = (int)($r['count'] ?? 0); }
?>
<?= Component::pageHeader('Reports', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Reports</span>') ?>

<!-- Export Bar -->
<div class="ss-card mb-4">
    <div class="ss-card-body d-flex flex-wrap gap-2 align-items-center">
        <span style="font-size:0.85rem;font-weight:600;margin-right:auto;">Export Report:</span>
        <button class="ss-btn ss-btn-light ss-btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <button class="ss-btn ss-btn-light ss-btn-sm" onclick="window.ssToast && ssToast.show('PDF export started...', 'info')"><i class="fas fa-file-pdf"></i> PDF</button>
        <button class="ss-btn ss-btn-light ss-btn-sm" onclick="window.ssToast && ssToast.show('Excel export started...', 'info')"><i class="fas fa-file-excel"></i> Excel</button>
        <button class="ss-btn ss-btn-light ss-btn-sm" onclick="window.ssToast && ssToast.show('CSV export started...', 'info')"><i class="fas fa-file-csv"></i> CSV</button>
    </div>
</div>

<!-- Summary -->
<div class="ss-card ss-card-gradient mb-4">
    <div class="ss-card-body">
        <h3 style="color:#fff;margin:0 0 0.5rem;">Platform Summary Report</h3>
        <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">Generated on <?= date('F j, Y \a\t g:i a') ?></p>
    </div>
</div>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-users', 'label' => 'Total Users', 'count' => (int)($s['total_users'] ?? 0), 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-briefcase', 'label' => 'Jobs', 'count' => (int)($s['active_jobs'] ?? 0), 'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-folder-open', 'label' => 'Applications', 'count' => (int)($s['total_applications'] ?? 0), 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-handshake', 'label' => 'Employment Rate', 'value' => $employmentRate . '%', 'color' => 'warning']) ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-users-cog text-primary"></i> Role Distribution</h5></div><div class="chart-canvas-wrap" style="height:260px;"><canvas id="roleDistChart"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-graduation-cap text-primary"></i> Students by Faculty</h5></div><div class="chart-canvas-wrap" style="height:260px;"><canvas id="deptChart"></canvas></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="ss-card">
            <div class="ss-card-header"><h3><i class="fas fa-university text-primary"></i> Top Universities</h3></div>
            <div class="ss-card-body" style="padding:1rem 1.25rem;">
                <?php foreach ($topUniversities ?? [] as $i => $uni): ?>
                <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>"><div class="rank"><?= $i + 1 ?></div><div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-warm);"><i class="fas fa-university"></i></div><div style="flex:1;min-width:0;"><div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($uni['uni_name'] ?? '') ?></div><div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($uni['location'] ?? '') ?></div></div><span class="ss-badge ss-badge-primary"><?= (int)$uni['student_count'] ?> students</span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ss-card">
            <div class="ss-card-header"><h3><i class="fas fa-building text-primary"></i> Top Companies</h3></div>
            <div class="ss-card-body" style="padding:1rem 1.25rem;">
                <?php foreach ($topCompanies ?? [] as $i => $co): ?>
                <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>"><div class="rank"><?= $i + 1 ?></div><div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><i class="fas fa-building"></i></div><div style="flex:1;min-width:0;"><div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($co['company_name'] ?? '') ?></div><div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($co['industry'] ?? '') ?></div></div><span class="ss-badge ss-badge-success"><?= (int)$co['job_count'] ?> jobs</span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    if (typeof Chart === 'undefined') return;
    const c = window.SS_THEME || {};
    const rd = document.getElementById('roleDistChart');
    if (rd) new Chart(rd, { type: 'doughnut', data: { labels: <?= json_encode($rdLabels) ?>, datasets: [{ data: <?= json_encode($rdData) ?>, backgroundColor: [c.primary, c.secondary, c.warning, c.success, c.danger, c.accent], borderWidth: 3, borderColor: c.surface }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom' } } } });
    const dp = document.getElementById('deptChart');
    if (dp && <?= json_encode(!empty($deptLabels)) ?>) new Chart(dp, { type: 'bar', data: { labels: <?= json_encode($deptLabels) ?>, datasets: [{ data: <?= json_encode($deptData2) ?>, backgroundColor: c.primary, borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: c.grid } }, x: { grid: { display: false } } } } });
})();
</script>
