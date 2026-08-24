<?php
/**
 * Admin — Analytics
 * Data: $stats, $userGrowth, $appTrend, $jobsGrowth, $revenueData, $roleDist, $topSkills, $deptData, $topUniversities, $topCompanies, $employmentRate
 */
use App\Helpers\URL;
use App\Helpers\Component;
use App\Helpers\Theme;
$chartColors = Theme::chartColors();
$pageTitle = 'Analytics';
$s = $stats ?? [];

// Build chart data
$ugBucket = []; foreach ($userGrowth ?? [] as $r) { $m = date('M Y', strtotime($r['date'])); $ugBucket[$m] = ($ugBucket[$m] ?? 0) + (int)$r['count']; }
$ugLabels = array_keys($ugBucket); $ugData = array_values($ugBucket);
if (empty($ugLabels)) { for ($i = 11; $i >= 0; $i--) { $ugLabels[] = date('M Y', strtotime("-$i months")); $ugData[] = 0; } }

$jgBucket = []; foreach ($jobsGrowth ?? [] as $r) { $m = date('M Y', strtotime($r['month'] . '-01')); $jgBucket[$m] = ($jgBucket[$m] ?? 0) + (int)$r['count']; }
$jgLabels = array_keys($jgBucket); $jgData = array_values($jgBucket);

$revBucket = []; foreach ($revenueData ?? [] as $r) { $m = date('M Y', strtotime($r['month'] . '-01')); $revBucket[$m] = ($revBucket[$m] ?? 0) + (float)$r['total']; }
$revLabels = array_keys($revBucket); $revData = array_values($revBucket);

$atBucket = []; foreach ($appTrend ?? [] as $r) { $m = date('M Y', strtotime($r['date'])); $atBucket[$m] = ($atBucket[$m] ?? 0) + (int)$r['count']; }
$atLabels = array_keys($atBucket); $atData = array_values($atBucket);

$rdLabels = []; $rdData = []; foreach ($roleDist ?? [] as $r) { $rdLabels[] = $r['role_name'] ?? 'Unknown'; $rdData[] = (int)($r['count'] ?? 0); }
$skillLabels = []; $skillData = []; foreach ($topSkills ?? [] as $sk) { $skillLabels[] = $sk['name']; $skillData[] = (int)$sk['count']; }
$deptLabels = []; $deptData = []; foreach ($deptData ?? [] as $d) { $deptLabels[] = $d['department']; $deptData[] = (int)$d['count']; }
?>
<?= Component::pageHeader('Analytics', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Analytics</span>') ?>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-users', 'label' => 'Total Users', 'count' => (int)($s['total_users'] ?? 0), 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-briefcase', 'label' => 'Jobs', 'count' => (int)($s['active_jobs'] ?? 0), 'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-folder-open', 'label' => 'Applications', 'count' => (int)($s['total_applications'] ?? 0), 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-dollar-sign', 'label' => 'Revenue', 'value' => number_format($s['total_revenue'] ?? 0, 0) . ' RWF', 'color' => 'warning']) ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-chart-line text-primary"></i> User Registration Trend</h5></div><div class="chart-canvas-wrap" style="height:280px;"><canvas id="userGrowthChart"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-briefcase text-primary"></i> Jobs Posted</h5></div><div class="chart-canvas-wrap" style="height:280px;"><canvas id="jobsGrowthChart"></canvas></div></div>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-dollar-sign text-success"></i> Revenue</h5></div><div class="chart-canvas-wrap" style="height:280px;"><canvas id="revenueChart"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-chart-bar text-primary"></i> Applications</h5></div><div class="chart-canvas-wrap" style="height:280px;"><canvas id="appTrendChart"></canvas></div></div>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-users-cog text-primary"></i> Role Distribution</h5></div><div class="chart-canvas-wrap" style="height:240px;"><canvas id="roleDistChart"></canvas></div></div>
    </div>
    <div class="col-lg-4">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-code text-primary"></i> Top Skills</h5></div><div class="chart-canvas-wrap" style="height:240px;"><canvas id="skillsChart"></canvas></div></div>
    </div>
    <div class="col-lg-4">
        <div class="ss-chart-card"><div class="chart-header"><h5><i class="fas fa-graduation-cap text-primary"></i> By Faculty</h5></div><div class="chart-canvas-wrap" style="height:240px;"><canvas id="deptChart"></canvas></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="ss-card"><div class="ss-card-header"><h3><i class="fas fa-university text-primary"></i> Top Universities</h3></div><div class="ss-card-body" style="padding:1rem 1.25rem;">
            <?php foreach ($topUniversities ?? [] as $i => $uni): ?>
            <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>"><div class="rank"><?= $i + 1 ?></div><div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-warm);"><i class="fas fa-university"></i></div><div style="flex:1;min-width:0;"><div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($uni['uni_name'] ?? '') ?></div><div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($uni['location'] ?? '') ?></div></div><span class="ss-badge ss-badge-primary"><?= (int)$uni['student_count'] ?></span></div>
            <?php endforeach; ?>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="ss-card"><div class="ss-card-header"><h3><i class="fas fa-building text-primary"></i> Top Companies</h3></div><div class="ss-card-body" style="padding:1rem 1.25rem;">
            <?php foreach ($topCompanies ?? [] as $i => $co): ?>
            <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>"><div class="rank"><?= $i + 1 ?></div><div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><i class="fas fa-building"></i></div><div style="flex:1;min-width:0;"><div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($co['company_name'] ?? '') ?></div><div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($co['industry'] ?? '') ?></div></div><span class="ss-badge ss-badge-success"><?= (int)$co['job_count'] ?></span></div>
            <?php endforeach; ?>
        </div></div>
    </div>
</div>

<script>
(function() {
    if (typeof Chart === 'undefined') return;
    const c = <?= json_encode($chartColors) ?>;
    const grad = (ctx, col, h) => { const g = ctx.createLinearGradient(0, 0, 0, h || 300); g.addColorStop(0, col + '55'); g.addColorStop(1, col + '05'); return g; };
    const mkLine = (id, labels, data, col) => { const el = document.getElementById(id); if (!el) return; new Chart(el, { type: 'line', data: { labels, datasets: [{ data, borderColor: col, backgroundColor: grad(el.getContext('2d'), col), borderWidth: 3, tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: col }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: c.grid } }, x: { grid: { display: false } } } } }); };
    const mkBar = (id, labels, data, col) => { const el = document.getElementById(id); if (!el) return; new Chart(el, { type: 'bar', data: { labels, datasets: [{ data, backgroundColor: col, borderRadius: 6, maxBarThickness: 40 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: c.grid } }, x: { grid: { display: false } } } } }); };
    mkLine('userGrowthChart', <?= json_encode($ugLabels) ?>, <?= json_encode($ugData) ?>, c.primary);
    mkBar('jobsGrowthChart', <?= json_encode($jgLabels) ?>, <?= json_encode($jgData) ?>, c.primary);
    mkLine('revenueChart', <?= json_encode($revLabels) ?>, <?= json_encode($revData) ?>, c.success);
    mkBar('appTrendChart', <?= json_encode($atLabels) ?>, <?= json_encode($atData) ?>, c.secondary);
    const rd = document.getElementById('roleDistChart'); if (rd) new Chart(rd, { type: 'doughnut', data: { labels: <?= json_encode($rdLabels) ?>, datasets: [{ data: <?= json_encode($rdData) ?>, backgroundColor: [c.primary, c.secondary, c.warning, c.success, c.danger, c.accent], borderWidth: 3, borderColor: c.surface }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } } });
    const sk = document.getElementById('skillsChart'); if (sk && <?= json_encode(!empty($skillLabels)) ?>) new Chart(sk, { type: 'bar', data: { labels: <?= json_encode($skillLabels) ?>, datasets: [{ data: <?= json_encode($skillData) ?>, backgroundColor: c.accent, borderRadius: 4 }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: c.grid } }, y: { grid: { display: false } } } } });
    const dp = document.getElementById('deptChart'); if (dp && <?= json_encode(!empty($deptLabels)) ?>) new Chart(dp, { type: 'bar', data: { labels: <?= json_encode($deptLabels) ?>, datasets: [{ data: <?= json_encode($deptData) ?>, backgroundColor: [c.primary, c.secondary, c.accent, c.success, c.warning, c.danger, c.info, '#7C3AED'], borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: c.grid } }, x: { grid: { display: false } } } } });
})();
</script>
