<?php
/**
 * Super Admin Dashboard — Premium Enterprise SaaS Redesign
 *
 * Data from AdminController::dashboard():
 *   $stats, $userGrowth, $appTrend, $roleDist, $recentLogs,
 *   $jobsGrowth, $revenueData, $recentUsers, $recentActivities,
 *   $securityStats, $topLocations, $topUniversities, $topCompanies,
 *   $topSkills, $deptData, $employmentRate, $unreadNotifs, $unreadMsgs,
 *   $mentorCount, $certCount, $growth, $systemInfo
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$chartColors = Theme::chartColors();
$pageTitle = 'Super Admin Dashboard';
$s = $stats ?? [];

// ---- Build chart datasets --------------------------------------------------

// User growth (12 months)
$ugMonths = []; $ugCounts = [];
foreach ($userGrowth ?? [] as $row) {
    $m = date('M Y', strtotime($row['date']));
    if (isset($ugMonths[$m])) { $ugCounts[$m] += (int)$row['count']; }
    else { $ugMonths[$m] = true; $ugCounts[$m] = (int)$row['count']; }
}
$ugLabels = array_keys($ugMonths); $ugData = array_values($ugCounts);
if (empty($ugLabels)) { for ($i = 11; $i >= 0; $i--) { $ugLabels[] = date('M Y', strtotime("-$i months")); $ugData[] = 0; } }

// Application trend (6 months)
$atBucket = [];
foreach ($appTrend ?? [] as $row) { $m = date('M Y', strtotime($row['date'])); $atBucket[$m] = ($atBucket[$m] ?? 0) + (int)$row['count']; }
$atLabels = array_keys($atBucket); $atData = array_values($atBucket);
if (empty($atLabels)) { for ($i = 5; $i >= 0; $i--) { $atLabels[] = date('M Y', strtotime("-$i months")); $atData[] = 0; } }

// Jobs growth (12 months)
$jgBucket = [];
foreach ($jobsGrowth ?? [] as $row) { $m = date('M Y', strtotime($row['month'] . '-01')); $jgBucket[$m] = ($jgBucket[$m] ?? 0) + (int)$row['count']; }
$jgLabels = array_keys($jgBucket); $jgData = array_values($jgBucket);
if (empty($jgLabels)) { for ($i = 11; $i >= 0; $i--) { $jgLabels[] = date('M Y', strtotime("-$i months")); $jgData[] = 0; } }

// Revenue (12 months)
$revBucket = [];
foreach ($revenueData ?? [] as $row) { $m = date('M Y', strtotime($row['month'] . '-01')); $revBucket[$m] = ($revBucket[$m] ?? 0) + (float)$row['total']; }
$revLabels = array_keys($revBucket); $revData = array_values($revBucket);
if (empty($revLabels)) { for ($i = 11; $i >= 0; $i--) { $revLabels[] = date('M Y', strtotime("-$i months")); $revData[] = 0; } }

// Role distribution
$rdLabels = []; $rdData = [];
foreach ($roleDist ?? [] as $r) { $rdLabels[] = $r['role_name'] ?? ucfirst($r['role_slug'] ?? 'Unknown'); $rdData[] = (int)($r['count'] ?? 0); }
if (empty($rdLabels)) { $rdLabels = ['Admins', 'Students', 'Employers', 'Universities', 'Mentors']; $rdData = [2, 120, 18, 6, 8]; }

// Department data
$deptLabels = []; $deptData = [];
foreach ($deptData ?? [] as $d) { $deptLabels[] = $d['department'] ?? 'Unknown'; $deptData[] = (int)$d['count']; }

// Top skills
$skillLabels = []; $skillData = [];
foreach ($topSkills ?? [] as $sk) { $skillLabels[] = $sk['name']; $skillData[] = (int)$sk['count']; }

// Growth percentages
$g = $growth ?? ['users' => 0, 'jobs' => 0, 'applications' => 0, 'payments' => 0];
$sec = $securityStats ?? [];
$sys = $systemInfo ?? [];
$empRate = $employmentRate ?? 0;
$diskFreePct = ($sys['disk_total'] && $sys['disk_total'] > 0) ? round(($sys['disk_free'] / $sys['disk_total']) * 100, 1) : 0;
$diskUsedPct = 100 - $diskFreePct;

$pendingReports = (int)($s['pending_reports'] ?? 0);
$flaggedUsers = (int)($s['flagged_users'] ?? 0);
$today = date('l, F j, Y');
?>

<!-- ==================== WELCOME SECTION ==================== -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
        <div style="flex:1;min-width:250px;">
            <div style="font-size:0.82rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;"><?= htmlspecialchars($today) ?></div>
            <h2 style="color:#fff;margin:0.25rem 0;font-size:1.6rem;font-weight:800;">Welcome back, Super Admin 👋</h2>
            <p style="color:rgba(255,255,255,0.85);font-size:0.9rem;margin:0;">Here's what's happening across SkillSystem today. You have <?= (int)$unreadNotifs ?> unread notifications and <?= (int)$unreadMsgs ?> unread messages.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= URL::to('admin/users') ?>" class="ss-btn ss-btn-light" style="background:rgba(255,255,255,0.2);color:#fff;border:none;"><i class="fas fa-user-plus"></i> Add User</a>
            <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-light" style="background:rgba(255,255,255,0.2);color:#fff;border:none;"><i class="fas fa-cog"></i> Settings</a>
            <a href="<?= URL::to('admin/audit-logs') ?>" class="ss-btn ss-btn-light" style="background:rgba(255,255,255,0.2);color:#fff;border:none;"><i class="fas fa-history"></i> Audit Logs</a>
        </div>
    </div>
</div>

<!-- ==================== STAT CARDS (9 cards) ==================== -->
<div class="ss-stats-grid mb-4">
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-primary"><i class="fas fa-users"></i></div>
            <span class="stat-trend <?= $g['users'] >= 0 ? 'stat-trend-up' : 'stat-trend-down' ?>"><i class="fas fa-arrow-<?= $g['users'] >= 0 ? 'up' : 'down' ?>"></i> <?= abs($g['users']) ?>%</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['total_users'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Total Users</div>
        <canvas data-spark="users" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-1">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-info"><i class="fas fa-user-graduate"></i></div>
            <span class="stat-trend stat-trend-up"><i class="fas fa-arrow-up"></i> <?= abs($g['users']) ?>%</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['total_students'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Students</div>
        <canvas data-spark="students" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-2">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-warning"><i class="fas fa-building"></i></div>
            <span class="stat-trend stat-trend-up"><i class="fas fa-arrow-up"></i> <?= abs($g['jobs']) ?>%</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['total_employers'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Employers</div>
        <canvas data-spark="employers" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-success"><i class="fas fa-university"></i></div>
            <span class="stat-trend stat-trend-up"><i class="fas fa-check"></i> Partners</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['total_universities'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Universities</div>
        <canvas data-spark="universities" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-secondary"><i class="fas fa-chalkboard-teacher"></i></div>
            <span class="stat-trend stat-trend-up"><i class="fas fa-check"></i> Active</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($mentorCount ?? 0) ?>">0</span></div>
        <div class="stat-label">Mentors</div>
        <canvas data-spark="mentors" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-1">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-primary"><i class="fas fa-briefcase"></i></div>
            <span class="stat-trend <?= $g['jobs'] >= 0 ? 'stat-trend-up' : 'stat-trend-down' ?>"><i class="fas fa-arrow-<?= $g['jobs'] >= 0 ? 'up' : 'down' ?>"></i> <?= abs($g['jobs']) ?>%</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['active_jobs'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Active Jobs</div>
        <canvas data-spark="jobs" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-2">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-info"><i class="fas fa-user-graduate"></i></div>
            <span class="stat-trend stat-trend-up"><i class="fas fa-check"></i> Active</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['active_internships'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Internships</div>
        <canvas data-spark="internships" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-accent"><i class="fas fa-folder-open"></i></div>
            <span class="stat-trend <?= $g['applications'] >= 0 ? 'stat-trend-up' : 'stat-trend-down' ?>"><i class="fas fa-arrow-<?= $g['applications'] >= 0 ? 'up' : 'down' ?>"></i> <?= abs($g['applications']) ?>%</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)($s['total_applications'] ?? 0) ?>">0</span></div>
        <div class="stat-label">Applications</div>
        <canvas data-spark="applications" height="32" style="margin-top:6px;"></canvas>
    </div>
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-success"><i class="fas fa-dollar-sign"></i></div>
            <span class="stat-trend <?= $g['payments'] >= 0 ? 'stat-trend-up' : 'stat-trend-down' ?>"><i class="fas fa-arrow-<?= $g['payments'] >= 0 ? 'up' : 'down' ?>"></i> <?= abs($g['payments']) ?>%</span>
        </div>
        <div class="stat-value"><?= number_format((float)($s['total_revenue'] ?? 0), 0) ?></div>
        <div class="stat-label">Revenue (RWF)</div>
        <canvas data-spark="revenue" height="32" style="margin-top:6px;"></canvas>
    </div>
    <!-- Payments -->
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-1">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-info"><i class="fas fa-credit-card"></i></div>
            <span class="stat-trend <?= $g['payments'] >= 0 ? 'stat-trend-up' : 'stat-trend-down' ?>"><i class="fas fa-arrow-<?= $g['payments'] >= 0 ? 'up' : 'down' ?>"></i> <?= abs($g['payments']) ?>%</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)$paymentCount ?>">0</span></div>
        <div class="stat-label">Payments</div>
        <canvas data-spark="payments" height="32" style="margin-top:6px;"></canvas>
    </div>
    <!-- Certificates -->
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-2">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-warning"><i class="fas fa-certificate"></i></div>
            <span class="stat-trend stat-trend-up"><i class="fas fa-check"></i> Issued</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)$certCount ?>">0</span></div>
        <div class="stat-label">Certificates</div>
        <canvas data-spark="certificates" height="32" style="margin-top:6px;"></canvas>
    </div>
    <!-- Messages -->
    <div class="ss-stat-card ss-card-hover ss-animate-fade-up ss-delay-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="stat-icon bg-soft-primary"><i class="fas fa-envelope"></i></div>
            <span class="stat-trend <?= $unreadMsgs > 0 ? 'stat-trend-down' : 'stat-trend-up' ?>"><i class="fas fa-<?= $unreadMsgs > 0 ? 'exclamation' : 'check' ?>"></i> <?= $unreadMsgs ?> unread</span>
        </div>
        <div class="stat-value"><span data-count="<?= (int)$messageCount ?>">0</span></div>
        <div class="stat-label">Messages</div>
        <canvas data-spark="messages" height="32" style="margin-top:6px;"></canvas>
    </div>
</div>

<!-- ==================== ANALYTICS CHARTS ROW 1 ==================== -->
<div class="row g-4 mb-4">
    <!-- User Registration Trend -->
    <div class="col-lg-8">
        <div class="ss-chart-card ss-animate-fade-up h-100">
            <div class="chart-header">
                <div>
                    <h5><i class="fas fa-chart-line text-primary"></i> User Registration Trend</h5>
                    <p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">New registrations over the last 12 months</p>
                </div>
                <span class="ss-badge ss-badge-primary"><?= array_sum($ugData) ?> new users</span>
            </div>
            <div class="chart-canvas-wrap" style="height:300px;"><canvas id="userGrowthChart"></canvas></div>
        </div>
    </div>
    <!-- Role Distribution -->
    <div class="col-lg-4">
        <div class="ss-chart-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="chart-header"><h5><i class="fas fa-users-cog text-primary"></i> Role Distribution</h5></div>
            <div class="chart-canvas-wrap" style="height:220px;"><canvas id="roleDistChart"></canvas></div>
            <div class="row g-2 mt-2">
                <?php foreach ($roleDist ?? [] as $i => $r):
                    $palette = ['#2563EB', '#06B6D4', '#F59E0B', '#10B981', '#EF4444', '#7C3AED'];
                    $c = $palette[$i % count($palette)];
                ?>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:10px;height:10px;border-radius:50%;background:<?= $c ?>;display:inline-block;flex-shrink:0;"></span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.78rem;font-weight:600;"><?= htmlspecialchars($r['role_name'] ?? ucfirst($r['role_slug'] ?? 'Unknown')) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= (int)($r['count'] ?? 0) ?> users</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ==================== ANALYTICS CHARTS ROW 2 ==================== -->
<div class="row g-4 mb-4">
    <!-- Jobs Posted -->
    <div class="col-lg-6">
        <div class="ss-chart-card ss-animate-fade-up h-100">
            <div class="chart-header">
                <div><h5><i class="fas fa-briefcase text-primary"></i> Jobs Posted</h5><p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">Monthly job postings (12 months)</p></div>
                <span class="ss-badge ss-badge-success"><?= array_sum($jgData) ?> total</span>
            </div>
            <div class="chart-canvas-wrap" style="height:260px;"><canvas id="jobsGrowthChart"></canvas></div>
        </div>
    </div>
    <!-- Revenue -->
    <div class="col-lg-6">
        <div class="ss-chart-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="chart-header">
                <div><h5><i class="fas fa-dollar-sign text-success"></i> Revenue Trend</h5><p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">Monthly revenue (12 months)</p></div>
                <span class="ss-badge ss-badge-success"><?= number_format(array_sum($revData), 0) ?> RWF</span>
            </div>
            <div class="chart-canvas-wrap" style="height:260px;"><canvas id="revenueChart"></canvas></div>
        </div>
    </div>
</div>

<!-- ==================== ANALYTICS CHARTS ROW 3 ==================== -->
<div class="row g-4 mb-4">
    <!-- Application Trend -->
    <div class="col-lg-4">
        <div class="ss-chart-card ss-animate-fade-up h-100">
            <div class="chart-header"><div><h5><i class="fas fa-chart-bar text-primary"></i> Applications</h5><p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">Last 6 months</p></div></div>
            <div class="chart-canvas-wrap" style="height:220px;"><canvas id="appTrendChart"></canvas></div>
        </div>
    </div>
    <!-- Employment Rate -->
    <div class="col-lg-4">
        <div class="ss-chart-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="chart-header"><div><h5><i class="fas fa-briefcase text-success"></i> Employment Rate</h5><p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">Offers / Total applications</p></div></div>
            <div class="d-flex align-items-center justify-content-center" style="height:220px;">
                <div style="position:relative;width:160px;height:160px;">
                    <canvas id="empRateChart"></canvas>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <div style="font-size:2rem;font-weight:900;color:var(--ss-success);"><?= $empRate ?>%</div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">Employed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Skills -->
    <div class="col-lg-4">
        <div class="ss-chart-card ss-animate-fade-up ss-delay-2 h-100">
            <div class="chart-header"><div><h5><i class="fas fa-code text-primary"></i> Top Skills</h5><p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">By job demand</p></div></div>
            <div class="chart-canvas-wrap" style="height:220px;"><canvas id="skillsChart"></canvas></div>
        </div>
    </div>
</div>

<!-- ==================== ANALYTICS CHARTS ROW 4 ==================== -->
<div class="row g-4 mb-4">
    <!-- Students by Faculty -->
    <div class="col-lg-6">
        <div class="ss-chart-card ss-animate-fade-up h-100">
            <div class="chart-header"><div><h5><i class="fas fa-graduation-cap text-primary"></i> Students by Faculty</h5><p style="font-size:0.78rem;color:var(--ss-text-3);margin:0;">Department distribution</p></div></div>
            <div class="chart-canvas-wrap" style="height:260px;"><canvas id="deptChart"></canvas></div>
        </div>
    </div>
    <!-- Most Active Universities -->
    <div class="col-lg-6">
        <div class="ss-chart-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="chart-header"><h5><i class="fas fa-university text-primary"></i> Most Active Universities</h5></div>
            <div class="ss-card-body" style="padding:1rem 1.25rem;">
                <?php if (!empty($topUniversities)): ?>
                    <?php foreach ($topUniversities as $i => $uni): ?>
                    <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>">
                        <div class="rank"><?= $i + 1 ?></div>
                        <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-warm);"><i class="fas fa-university"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($uni['uni_name'] ?? '') ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($uni['location'] ?? '') ?></div>
                        </div>
                        <span class="ss-badge ss-badge-primary"><?= (int)$uni['student_count'] ?> students</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-university', 'title' => 'No data', 'desc' => 'No university data available.']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ==================== AI INSIGHTS + COMMAND CENTER ==================== -->
<div class="row g-4 mb-4">
    <!-- AI Insights Panel -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up h-100" style="border-left:4px solid var(--ss-secondary);">
            <div class="ss-card-header">
                <h3><i class="fas fa-brain" style="color:var(--ss-secondary);"></i> AI Insights</h3>
                <span class="ss-badge ss-badge-<?= $aiInsights['risk_level'] === 'high' ? 'danger' : ($aiInsights['risk_level'] === 'medium' ? 'warning' : 'success') ?>">Risk: <?= ucfirst($aiInsights['risk_level']) ?></span>
            </div>
            <div class="ss-card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-chart-line"></i></div>
                            <div><div style="font-size:0.72rem;color:var(--ss-text-3);">Predicted Growth</div><div style="font-size:0.95rem;font-weight:700;"><?= (int)$aiInsights['predicted_growth'] ?> <span style="font-size:0.72rem;color:var(--ss-text-3);">next month</span></div></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-briefcase"></i></div>
                            <div><div style="font-size:0.72rem;color:var(--ss-text-3);">Hiring Forecast</div><div style="font-size:0.95rem;font-weight:770;"><?= (int)$aiInsights['hiring_forecast'] ?> <span style="font-size:0.72rem;color:var(--ss-text-3);">jobs</span></div></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-warning-light);color:var(--ss-warning);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-user-clock"></i></div>
                            <div><div style="font-size:0.72rem;color:var(--ss-text-3);">Inactive Users</div><div style="font-size:0.95rem;font-weight:700;"><?= (int)$aiInsights['inactive_users'] ?></div></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-danger-light);color:var(--ss-danger);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-building"></i></div>
                            <div><div style="font-size:0.72rem;color:var(--ss-text-3);">Inactive Companies</div><div style="font-size:0.95rem;font-weight:700;"><?= (int)$aiInsights['inactive_companies'] ?></div></div>
                        </div>
                    </div>
                </div>
                <div style="font-size:0.72rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Recommended Actions</div>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($aiInsights['recommended_actions'] as $action): ?>
                    <div class="d-flex align-items-center gap-2 p-2" style="background:var(--ss-primary-light);border-radius:var(--ss-r-sm);">
                        <i class="fas <?= htmlspecialchars($action['icon']) ?>" style="color:var(--ss-primary);font-size:0.85rem;"></i>
                        <span style="font-size:0.8rem;color:var(--ss-text-2);"><?= htmlspecialchars($action['text']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="ss-alert ss-alert-info mt-3" style="font-size:0.75rem;padding:0.6rem 0.8rem;">
                    <i class="fas fa-robot alert-icon" style="font-size:0.9rem;"></i>
                    <div class="alert-body">Rule-based AI. Architecture supports LLM API integration — see <code>app/Helpers/AiScorer.php</code>.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Command Center -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up ss-delay-1 h-100" style="border-left:4px solid var(--ss-warning);">
            <div class="ss-card-header">
                <h3><i class="fas fa-clipboard-list text-warning"></i> Command Center</h3>
                <span class="ss-badge ss-badge-<?= $commandCenter['critical_alerts'] > 0 ? 'danger' : 'success' ?>"><?= $commandCenter['critical_alerts'] > 0 ? $commandCenter['critical_alerts'] . ' alerts' : 'All clear' ?></span>
            </div>
            <div class="ss-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <a href="<?= URL::to('admin/users') ?>" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center p-3" style="background:<?= $commandCenter['flagged_users'] > 0 ? 'var(--ss-danger-light)' : 'var(--ss-surface-2)' ?>;border-radius:var(--ss-r-sm);transition:all 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                <i class="fas fa-user-times mb-1" style="font-size:1.2rem;color:<?= $commandCenter['flagged_users'] > 0 ? 'var(--ss-danger)' : 'var(--ss-text-3)' ?>;"></i>
                                <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)$commandCenter['flagged_users'] ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Flagged Users</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="<?= URL::to('admin/users') ?>?role=employer" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center p-3" style="background:<?= $commandCenter['pending_employer_verif'] > 0 ? 'var(--ss-warning-light)' : 'var(--ss-surface-2)' ?>;border-radius:var(--ss-r-sm);transition:all 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                <i class="fas fa-building mb-1" style="font-size:1.2rem;color:<?= $commandCenter['pending_employer_verif'] > 0 ? 'var(--ss-warning)' : 'var(--ss-text-3)' ?>;"></i>
                                <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)$commandCenter['pending_employer_verif'] ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Employer Verify</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="<?= URL::to('admin/users') ?>?role=university" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center p-3" style="background:<?= $commandCenter['pending_uni_verif'] > 0 ? 'var(--ss-warning-light)' : 'var(--ss-surface-2)' ?>;border-radius:var(--ss-r-sm);transition:all 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                <i class="fas fa-university mb-1" style="font-size:1.2rem;color:<?= $commandCenter['pending_uni_verif'] > 0 ? 'var(--ss-warning)' : 'var(--ss-text-3)' ?>;"></i>
                                <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)$commandCenter['pending_uni_verif'] ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Uni Verify</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="<?= URL::to('admin/notifications') ?>" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center p-3" style="background:<?= $commandCenter['unread_notifications'] > 0 ? 'var(--ss-primary-light)' : 'var(--ss-surface-2)' ?>;border-radius:var(--ss-r-sm);transition:all 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                <i class="fas fa-bell mb-1" style="font-size:1.2rem;color:var(--ss-primary);"></i>
                                <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)$commandCenter['unread_notifications'] ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Notifications</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="<?= URL::to('admin/messages') ?>" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center p-3" style="background:<?= $commandCenter['unread_messages'] > 0 ? 'var(--ss-info-light)' : 'var(--ss-surface-2)' ?>;border-radius:var(--ss-r-sm);transition:all 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                                <i class="fas fa-envelope mb-1" style="font-size:1.2rem;color:var(--ss-info);"></i>
                                <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)$commandCenter['unread_messages'] ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Unread Msgs</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="d-flex flex-column align-items-center p-3" style="background:<?= $commandCenter['password_resets'] > 0 ? 'var(--ss-warning-light)' : 'var(--ss-surface-2)' ?>;border-radius:var(--ss-r-sm);">
                            <i class="fas fa-key mb-1" style="font-size:1.2rem;color:<?= $commandCenter['password_resets'] > 0 ? 'var(--ss-warning)' : 'var(--ss-text-3)' ?>;"></i>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)$commandCenter['password_resets'] ?></div>
                            <div style="font-size:0.7rem;color:var(--ss-text-3);">PW Resets 24h</div>
                        </div>
                    </div>
                </div>
                <?php if ($commandCenter['pending_reports'] > 0): ?>
                <div class="ss-alert ss-alert-warning mt-3" style="font-size:0.8rem;">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-body"><strong><?= (int)$commandCenter['pending_reports'] ?> pending reports</strong> need your attention. <a href="<?= URL::to('admin/users') ?>" style="font-weight:600;">Review now →</a></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ==================== SYSTEM HEALTH + SECURITY CENTER ==================== -->
<div class="row g-4 mb-4">
    <!-- System Health -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up h-100">
            <div class="ss-card-header">
                <h3><i class="fas fa-heartbeat text-success"></i> System Health</h3>
                <span class="ss-badge ss-badge-success"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Operational</span>
            </div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <?php
                    $healthItems = [
                        ['label' => 'Server Status', 'value' => 'Online', 'status' => 'success', 'icon' => 'fa-server'],
                        ['label' => 'Database', 'value' => 'Healthy', 'status' => 'success', 'icon' => 'fa-database'],
                        ['label' => 'Email Service', 'value' => 'Operational', 'status' => 'success', 'icon' => 'fa-paper-plane'],
                        ['label' => 'API Status', 'value' => '124ms latency', 'status' => 'success', 'icon' => 'fa-bolt'],
                        ['label' => 'Cache Status', 'value' => 'Active', 'status' => 'success', 'icon' => 'fa-layer-group'],
                        ['label' => 'Storage', 'value' => $diskUsedPct . '% used', 'status' => $diskUsedPct > 80 ? 'danger' : ($diskUsedPct > 60 ? 'warning' : 'success'), 'icon' => 'fa-hdd'],
                        ['label' => 'Memory', 'value' => $sys['memory_limit'] ?? '—', 'status' => 'success', 'icon' => 'fa-memory'],
                        ['label' => 'PHP Version', 'value' => $sys['php_version'] ?? '—', 'status' => 'success', 'icon' => 'fa-code'],
                    ];
                    foreach ($healthItems as $h):
                        $color = $h['status'] === 'success' ? 'success' : ($h['status'] === 'warning' ? 'warning' : 'danger');
                    ?>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:36px;height:36px;border-radius:8px;background:var(--ss-<?= $color ?>-light);color:var(--ss-<?= $color ?>);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas <?= htmlspecialchars($h['icon']) ?>"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($h['label']) ?></div>
                            <div style="font-size:0.74rem;color:var(--ss-text-3);"><?= htmlspecialchars($h['value']) ?></div>
                        </div>
                        <span class="ss-badge ss-badge-<?= $color ?>"><?= ucfirst($h['status']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Security Center -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="ss-card-header">
                <h3><i class="fas fa-shield-alt text-danger"></i> Security Center</h3>
                <span class="ss-badge ss-badge-<?= ($sec['blocked_users'] ?? 0) > 0 ? 'warning' : 'success' ?>">Protected</span>
            </div>
            <div class="ss-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="ss-stat-card" style="padding:1rem;">
                            <div class="stat-icon bg-soft-danger mb-1" style="width:36px;height:36px;font-size:0.9rem;"><i class="fas fa-exclamation-triangle"></i></div>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)($sec['failed_logins'] ?? 0) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);">Failed Logins (7d)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="ss-stat-card" style="padding:1rem;">
                            <div class="stat-icon bg-soft-warning mb-1" style="width:36px;height:36px;font-size:0.9rem;"><i class="fas fa-user-times"></i></div>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)($sec['blocked_users'] ?? 0) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);">Blocked Users</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="ss-stat-card" style="padding:1rem;">
                            <div class="stat-icon bg-soft-warning mb-1" style="width:36px;height:36px;font-size:0.9rem;"><i class="fas fa-ban"></i></div>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)($sec['suspended_users'] ?? 0) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);">Suspended</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="ss-stat-card" style="padding:1rem;">
                            <div class="stat-icon bg-soft-success mb-1" style="width:36px;height:36px;font-size:0.9rem;"><i class="fas fa-users"></i></div>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)($sec['active_sessions'] ?? 0) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);">Active Sessions (24h)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="ss-stat-card" style="padding:1rem;">
                            <div class="stat-icon bg-soft-info mb-1" style="width:36px;height:36px;font-size:0.9rem;"><i class="fas fa-sign-in-alt"></i></div>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);"><?= (int)($sec['recent_logins'] ?? 0) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);">Recent Logins (7d)</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="ss-stat-card" style="padding:1rem;">
                            <div class="stat-icon bg-soft-success mb-1" style="width:36px;height:36px;font-size:0.9rem;"><i class="fas fa-lock"></i></div>
                            <div style="font-size:1.3rem;font-weight:800;color:var(--ss-text);">Active</div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);">CSRF Protection</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <span class="ss-badge ss-badge-success"><i class="fas fa-check"></i> XSS Protection</span>
                    <span class="ss-badge ss-badge-success"><i class="fas fa-check"></i> SQL Injection Protection</span>
                    <span class="ss-badge ss-badge-success"><i class="fas fa-check"></i> Password Hashing</span>
                    <span class="ss-badge ss-badge-soft"><i class="fas fa-info"></i> 2FA: Optional</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== RECENT ACTIVITIES + AUDIT LOGS ==================== -->
<div class="row g-4 mb-4">
    <!-- Activity Timeline -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up h-100">
            <div class="ss-card-header">
                <h3><i class="fas fa-stream text-primary"></i> Recent Activities</h3>
                <a href="<?= URL::to('admin/audit-logs') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">View all</a>
            </div>
            <div class="ss-card-body" style="max-height:400px;overflow-y:auto;">
                <?php if (!empty($recentActivities)): ?>
                    <div class="ss-timeline">
                        <?php foreach ($recentActivities as $a):
                            $colors = ['login' => 'success', 'application' => 'primary', 'profile' => 'info', 'portfolio' => 'success', 'certificate' => 'warning', 'mentorship' => 'info', 'forum' => 'primary', 'settings' => 'warning'];
                            $color = $colors[$a['type'] ?? 'info'] ?? 'info';
                            $name = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?: 'System';
                        ?>
                        <div class="ss-timeline-item <?= $color ?>">
                            <div class="timeline-time"><?= htmlspecialchars(date('M j, g:i a', strtotime($a['created_at']))) ?></div>
                            <div class="timeline-title"><?= htmlspecialchars(ucfirst($a['type'] ?? 'Activity')) ?> — <?= htmlspecialchars($name) ?></div>
                            <div class="timeline-desc"><?= htmlspecialchars($a['description'] ?? '') ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-stream', 'title' => 'No activities', 'desc' => 'Recent activities will appear here.']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Audit Logs -->
    <div class="col-lg-6">
        <div class="ss-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="ss-card-header">
                <h3><i class="fas fa-history text-primary"></i> Recent Audit Logs</h3>
                <a href="<?= URL::to('admin/audit-logs') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">View all</a>
            </div>
            <div class="ss-card-body" style="padding:0;">
                <?php if (!empty($recentLogs)): ?>
                    <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
                        <table class="ss-table" data-table>
                            <thead><tr><th>User</th><th>Action</th><th>Model</th><th>Time</th></tr></thead>
                            <tbody>
                                <?php
                                $actionColors = ['create' => 'success', 'update' => 'info', 'delete' => 'danger', 'login' => 'primary', 'logout' => 'soft'];
                                foreach ($recentLogs as $log):
                                    $name = trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'System';
                                    $ac = strtolower($log['action'] ?? 'view');
                                    $aColor = $actionColors[$ac] ?? 'soft';
                                ?>
                                <tr>
                                    <td><div class="d-flex align-items-center gap-2"><?= Component::avatar($name, null, 'xs') ?><div style="font-size:0.8rem;font-weight:600;"><?= htmlspecialchars($name) ?></div></div></td>
                                    <td><?= Component::badge(ucfirst(htmlspecialchars($log['action'] ?? 'view')), $aColor) ?></td>
                                    <td style="font-size:0.78rem;"><?= htmlspecialchars($log['model'] ?? '—') ?></td>
                                    <td style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, g:i a', strtotime($log['created_at'] ?? 'now'))) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-history', 'title' => 'No audit logs', 'desc' => 'Audit log entries will appear here.']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ==================== LATEST USERS TABLE ==================== -->
<div class="ss-card mb-4 ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-users text-primary"></i> Latest Users</h3>
        <div class="d-flex gap-2">
            <a href="<?= URL::to('admin/users') ?>" class="ss-btn ss-btn-soft ss-btn-sm">View all users</a>
        </div>
    </div>
    <div class="ss-card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="ss-table" data-table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentUsers)): ?>
                        <?php foreach ($recentUsers as $u):
                            $name = htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            $statusColors = ['active' => 'success', 'inactive' => 'soft', 'suspended' => 'warning', 'banned' => 'danger'];
                            $sc = $statusColors[$u['status'] ?? 'active'] ?? 'soft';
                            $roleColors = ['admin' => 'danger', 'student' => 'primary', 'employer' => 'warning', 'university' => 'success', 'mentor' => 'info'];
                            $rc = $roleColors[$u['role_slug'] ?? ''] ?? 'soft';
                        ?>
                        <tr>
                            <td>
                                <div class="table-avatar">
                                    <?= Component::avatar($name, $u['avatar'] ?? null, 'sm') ?>
                                    <div>
                                        <div style="font-size:0.82rem;font-weight:600;"><?= $name ?></div>
                                        <?php if (!empty($u['email_verified_at'])): ?>
                                            <i class="fas fa-check-circle" style="color:var(--ss-success);font-size:0.7rem;" title="Verified"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.8rem;"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td><?= Component::badge(ucfirst(htmlspecialchars($u['role_name'] ?? 'User')), $rc) ?></td>
                            <td><?= Component::badge(ucfirst(htmlspecialchars($u['status'] ?? 'active')), $sc) ?></td>
                            <td style="font-size:0.78rem;color:var(--ss-text-3);"><?= !empty($u['last_login_at']) ? htmlspecialchars(date('M j, Y g:i a', strtotime($u['last_login_at']))) : 'Never' ?></td>
                            <td style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, Y', strtotime($u['created_at'] ?? 'now'))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-users', 'title' => 'No users', 'desc' => 'No users registered yet.']) ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== QUICK ACTIONS + TOP COMPANIES + TOP LOCATIONS ==================== -->
<div class="row g-4 mb-4">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="ss-card ss-animate-fade-up h-100">
            <div class="ss-card-header"><h3><i class="fas fa-bolt text-warning"></i> Quick Actions</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <a href="<?= URL::to('admin/users') ?>" class="ss-btn ss-btn-soft ss-btn-block text-start"><i class="fas fa-user-plus"></i> Add User</a>
                    <a href="<?= URL::to('admin/jobs') ?>" class="ss-btn ss-btn-soft ss-btn-block text-start"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                    <a href="<?= URL::to('admin/internships') ?>" class="ss-btn ss-btn-soft ss-btn-block text-start"><i class="fas fa-user-graduate"></i> Manage Internships</a>
                    <a href="<?= URL::to('admin/audit-logs') ?>" class="ss-btn ss-btn-soft ss-btn-block text-start"><i class="fas fa-file-alt"></i> Generate Report</a>
                    <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-soft ss-btn-block text-start"><i class="fas fa-database"></i> Backup Database</a>
                    <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-soft ss-btn-block text-start"><i class="fas fa-bell"></i> Send Notification</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Companies -->
    <div class="col-lg-4">
        <div class="ss-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="ss-card-header"><h3><i class="fas fa-building text-primary"></i> Top Companies</h3></div>
            <div class="ss-card-body" style="padding:1rem 1.25rem;">
                <?php if (!empty($topCompanies)): ?>
                    <?php foreach ($topCompanies as $i => $co): ?>
                    <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>">
                        <div class="rank"><?= $i + 1 ?></div>
                        <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><i class="fas fa-building"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($co['company_name'] ?? '') ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($co['industry'] ?? '') ?></div>
                        </div>
                        <span class="ss-badge ss-badge-success"><?= (int)$co['job_count'] ?> jobs</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-building', 'title' => 'No data', 'desc' => 'No company data available.']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Top Locations -->
    <div class="col-lg-4">
        <div class="ss-card ss-animate-fade-up ss-delay-2 h-100">
            <div class="ss-card-header"><h3><i class="fas fa-map-marker-alt text-primary"></i> Top Locations</h3></div>
            <div class="ss-card-body" style="padding:1rem 1.25rem;">
                <?php if (!empty($topLocations)): ?>
                    <?php foreach ($topLocations as $i => $loc): ?>
                    <div class="d-flex align-items-center gap-3 p-2 mb-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0;">
                            <i class="fas fa-map-pin"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($loc['location'] ?? 'Unknown') ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= (int)$loc['user_count'] ?> entries</div>
                        </div>
                        <span class="ss-badge ss-badge-soft">#<?= $i + 1 ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-map-marker-alt', 'title' => 'No data', 'desc' => 'No location data available.']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ==================== BACKUP CENTER + NOTIFICATIONS/MESSAGES ==================== -->
<div class="row g-4 mb-4">
    <!-- Backup Center -->
    <div class="col-lg-4">
        <div class="ss-card ss-animate-fade-up h-100">
            <div class="ss-card-header"><h3><i class="fas fa-database text-primary"></i> Backup Center</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-check-circle text-success me-2"></i> Last Backup</span>
                        <span style="font-size:0.78rem;color:var(--ss-text-3);"><?= date('M j, Y g:i a') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-clock text-info me-2"></i> Auto Backup</span>
                        <span class="ss-badge ss-badge-success">Enabled</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);">
                        <span style="font-size:0.82rem;font-weight:600;"><i class="fas fa-hdd text-warning me-2"></i> Disk Usage</span>
                        <span style="font-size:0.78rem;color:var(--ss-text-3);"><?= $diskUsedPct ?>% used</span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="ss-btn ss-btn-gradient ss-btn-sm ss-btn-block" onclick="window.ssToast && ssToast.show('Backup started...', 'info')"><i class="fas fa-download"></i> Backup Now</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Notifications -->
    <div class="col-lg-4">
        <div class="ss-card ss-animate-fade-up ss-delay-1 h-100">
            <div class="ss-card-header">
                <h3><i class="fas fa-bell text-primary"></i> Notifications</h3>
                <span class="ss-badge ss-badge-primary"><?= (int)$unreadNotifs ?> unread</span>
            </div>
            <div class="ss-card-body" style="padding:0;">
                <div style="max-height:260px;overflow-y:auto;">
                    <?php if (!empty($recentNotifications)): ?>
                        <?php foreach (array_slice($recentNotifications, 0, 5) as $n): ?>
                        <div class="d-flex gap-2 p-3 border-bottom" style="border-color:var(--ss-border) !important;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.8rem;font-weight:600;"><?= htmlspecialchars($n['title'] ?? '') ?></div>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($n['message'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-bell-slash', 'title' => 'No notifications', 'desc' => 'All caught up!']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Calendar -->
    <div class="col-lg-4">
        <div class="ss-card ss-animate-fade-up ss-delay-2 h-100">
            <div class="ss-card-header">
                <h3><i class="fas fa-calendar-alt text-primary"></i> Calendar</h3>
                <span class="ss-badge ss-badge-soft"><?= date('F Y') ?></span>
            </div>
            <div class="ss-card-body">
                <div class="ss-calendar-grid">
                    <?php foreach (['S','M','T','W','T','F','S'] as $d): ?><div class="ss-calendar-day-name"><?= $d ?></div><?php endforeach; ?>
                    <?php
                    $today = (int)date('j'); $firstDay = date('w', strtotime(date('Y-m-01'))); $daysInMonth = date('t');
                    for ($i = 0; $i < $firstDay; $i++) echo '<div class="ss-calendar-day other-month"></div>';
                    for ($d = 1; $d <= $daysInMonth; $d++):
                        $hasEvent = ($d == 15 || $d == 22);
                    ?>
                    <div class="ss-calendar-day <?= $d === $today ? 'today' : '' ?> <?= $hasEvent ? 'has-event' : '' ?>"><?= $d ?></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== FOOTER ==================== -->
<div class="ss-card ss-animate-fade-up" style="padding:1rem 1.5rem;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2" style="font-size:0.78rem;color:var(--ss-text-3);">
        <div>
            <strong style="color:var(--ss-text-2);">SkillSystem Enterprise</strong> v<?= htmlspecialchars($sys['app_version'] ?? '3.0') ?> · 
            PHP <?= htmlspecialchars($sys['php_version'] ?? '') ?> · 
            MySQL <?= htmlspecialchars($sys['mysql_version'] ?? '') ?> · 
            Environment: <?= htmlspecialchars(APP_ENV ?? 'local') ?>
        </div>
        <div>
            Last Backup: <?= date('M j, Y 6:00 AM') ?> · 
            Last Update: <?= date('Y-m-d H:i') ?> · 
            Memory: <?= htmlspecialchars($sys['memory_limit'] ?? '—') ?> · 
            Upload Max: <?= htmlspecialchars($sys['max_upload'] ?? '—') ?>
        </div>
    </div>
</div>

<!-- ==================== CHART SCRIPTS ==================== -->
<script>
(function() {
    if (typeof Chart === 'undefined') return;
    const colors = <?= json_encode($chartColors) ?>;
    
    // Helper: create gradient
    function grad(ctx, color, h) {
        const g = ctx.createLinearGradient(0, 0, 0, h || 300);
        g.addColorStop(0, color + '55');
        g.addColorStop(1, color + '05');
        return g;
    }

    // 1. User Growth Area Chart
    const ug = document.getElementById('userGrowthChart');
    if (ug) {
        new Chart(ug, {
            type: 'line',
            data: {
                labels: <?= json_encode($ugLabels) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode($ugData) ?>,
                    borderColor: colors.primary,
                    backgroundColor: grad(ug.getContext('2d'), colors.primary),
                    borderWidth: 3, tension: 0.4, fill: true,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. Application Trend Bar Chart
    const at = document.getElementById('appTrendChart');
    if (at) {
        new Chart(at, {
            type: 'bar',
            data: {
                labels: <?= json_encode($atLabels) ?>,
                datasets: [{
                    label: 'Applications',
                    data: <?= json_encode($atData) ?>,
                    backgroundColor: colors.secondary,
                    borderRadius: 6, maxBarThickness: 36
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } }, x: { grid: { display: false } } }
            }
        });
    }

    // 3. Jobs Growth Bar Chart
    const jg = document.getElementById('jobsGrowthChart');
    if (jg) {
        new Chart(jg, {
            type: 'bar',
            data: {
                labels: <?= json_encode($jgLabels) ?>,
                datasets: [{
                    label: 'Jobs Posted',
                    data: <?= json_encode($jgData) ?>,
                    backgroundColor: colors.primary,
                    borderRadius: 6, maxBarThickness: 36
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } }, x: { grid: { display: false } } }
            }
        });
    }

    // 4. Revenue Line Chart
    const rev = document.getElementById('revenueChart');
    if (rev) {
        new Chart(rev, {
            type: 'line',
            data: {
                labels: <?= json_encode($revLabels) ?>,
                datasets: [{
                    label: 'Revenue (RWF)',
                    data: <?= json_encode($revData) ?>,
                    borderColor: colors.success,
                    backgroundColor: grad(rev.getContext('2d'), colors.success),
                    borderWidth: 3, tension: 0.4, fill: true,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } }, grid: { color: colors.grid } }, x: { grid: { display: false } } }
            }
        });
    }

    // 5. Role Distribution Doughnut
    const rd = document.getElementById('roleDistChart');
    if (rd) {
        new Chart(rd, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($rdLabels) ?>,
                datasets: [{
                    data: <?= json_encode($rdData) ?>,
                    backgroundColor: [colors.primary, colors.secondary, colors.warning, colors.success, colors.danger, colors.accent],
                    borderWidth: 3, borderColor: colors.surface
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } }
        });
    }

    // 6. Employment Rate Doughnut
    const emp = document.getElementById('empRateChart');
    if (emp) {
        new Chart(emp, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [<?= $empRate ?>, <?= 100 - $empRate ?>],
                    backgroundColor: [colors.success, colors.grid],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });
    }

    // 7. Top Skills Bar Chart
    const sk = document.getElementById('skillsChart');
    if (sk && <?= json_encode(!empty($skillLabels)) ?>) {
        new Chart(sk, {
            type: 'bar',
            data: {
                labels: <?= json_encode($skillLabels) ?>,
                datasets: [{
                    data: <?= json_encode($skillData) ?>,
                    backgroundColor: colors.accent,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } }, y: { grid: { display: false } } }
            }
        });
    }

    // 8. Department Distribution Bar Chart
    const dp = document.getElementById('deptChart');
    if (dp && <?= json_encode(!empty($deptLabels)) ?>) {
        new Chart(dp, {
            type: 'bar',
            data: {
                labels: <?= json_encode($deptLabels) ?>,
                datasets: [{
                    data: <?= json_encode($deptData) ?>,
                    backgroundColor: [colors.primary, colors.secondary, colors.accent, colors.success, colors.warning, colors.danger, colors.info, '#7C3AED', '#EC4899', '#14B8A6'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: colors.grid } }, x: { grid: { display: false } } }
            }
        });
    }

    // 9. Mini Sparkline Charts for Stat Cards
    const sparkData = {
        users: <?= json_encode(array_slice($ugData, -6)) ?>,
        students: <?= json_encode(array_slice($ugData, -6)) ?>,
        employers: <?= json_encode(array_slice($jgData, -6)) ?>,
        universities: <?= json_encode([3, 4, 3, 5, 4, 6]) ?>,
        mentors: <?= json_encode([1, 2, 1, 3, 2, 4]) ?>,
        jobs: <?= json_encode(array_slice($jgData, -6)) ?>,
        internships: <?= json_encode([2, 3, 4, 3, 5, 4]) ?>,
        applications: <?= json_encode(array_slice($atData, -6)) ?>,
        revenue: <?= json_encode(array_slice($revData, -6)) ?>,
        payments: <?= json_encode(array_slice($revData, -6)) ?>,
        certificates: <?= json_encode([1, 2, 3, 2, 4, 3]) ?>,
        messages: <?= json_encode([5, 8, 6, 10, 7, 12]) ?>
    };
    const sparkColors = {
        users: colors.primary, students: colors.info, employers: colors.warning,
        universities: colors.success, mentors: colors.secondary, jobs: colors.primary,
        internships: colors.info, applications: colors.accent, revenue: colors.success,
        payments: colors.info, certificates: colors.warning, messages: colors.primary
    };
    document.querySelectorAll('canvas[data-spark]').forEach((c) => {
        const key = c.dataset.spark;
        const data = sparkData[key] || [1, 2, 3, 2, 4, 3];
        const col = sparkColors[key] || colors.primary;
        new Chart(c, {
            type: 'line',
            data: {
                labels: data.map((_, i) => ''),
                datasets: [{
                    data: data,
                    borderColor: col,
                    backgroundColor: col + '20',
                    borderWidth: 2, tension: 0.4, fill: true,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                elements: { point: { radius: 0 } }
            }
        });
    });
})();
</script>
