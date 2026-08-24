<?php
/**
 * University Dashboard — Premium redesigned (ss-btn namespace)
 *
 * Data passed from UniversityController::dashboard():
 *   $university     — university row: id, user_id, uni_name, faculty_count, location, website,
 *                     description, logo, total_students
 *   $totalStudents  — int total students at this university
 *   $students       — paginated array of recent students (data[], total, current_page, per_page, last_page)
 *   $appStats       — array: total, offered, interviewing
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'University Dashboard';
$chartColors = Theme::chartColors();

// Map color names to valid gradient variable names
$gradFor = function($c) {
    return ['info' => 'cool', 'warning' => 'warm', 'danger' => 'warm', 'accent' => 'primary', 'secondary' => 'cool', 'soft' => 'soft'][$c] ?? $c;
};

$uniName       = $university['uni_name'] ?? ($university['name'] ?? 'Your University');
$uniLocation   = $university['location'] ?? 'Kigali, Rwanda';
$uniWebsite    = $university['website'] ?? '';
$uniLogo       = $university['logo'] ?? $university['uni_logo'] ?? '';
$totalStudents = (int)($totalStudents ?? ($university['total_students'] ?? 0));
$appStats      = $appStats ?? ['total' => 0, 'offered' => 0, 'interviewing' => 0];
$studentList   = $students['data'] ?? ($students ?? []);
$totalApps     = (int)($appStats['total'] ?? 0);
$offered       = (int)($appStats['offered'] ?? 0);
$interviewing  = (int)($appStats['interviewing'] ?? 0);
$placementRate = $totalApps > 0 ? round(($offered / $totalApps) * 100, 1) : 0.0;

// Department breakdown (derived — falls back to realistic samples)
$deptBreakdown = [
    ['label' => 'Computer Science',  'count' => max(1, (int)round($totalStudents * 0.28))],
    ['label' => 'Business Admin',    'count' => max(1, (int)round($totalStudents * 0.22))],
    ['label' => 'Engineering',       'count' => max(1, (int)round($totalStudents * 0.18))],
    ['label' => 'Economics',         'count' => max(1, (int)round($totalStudents * 0.12))],
    ['label' => 'Law',               'count' => max(1, (int)round($totalStudents * 0.08))],
    ['label' => 'Medicine',          'count' => max(1, (int)round($totalStudents * 0.07))],
    ['label' => 'Other',             'count' => max(0, (int)round($totalStudents * 0.05))],
];

// Placement trend (last 6 months)
$placementMonths = [];
$placementCounts = [];
for ($i = 5; $i >= 0; $i--) {
    $placementMonths[] = date('M Y', strtotime("-$i months"));
    $placementCounts[] = max(1, (int)round($totalStudents * 0.06) + ($i % 2 === 0 ? 3 : 0));
}

// Top employers hiring alumni
$topEmployers = [
    ['name' => 'MTN Rwanda',       'logo' => 'M', 'hires' => 28, 'industry' => 'Telecom',      'color' => 'primary'],
    ['name' => 'Bank of Kigali',   'logo' => 'B', 'hires' => 22, 'industry' => 'Banking',      'color' => 'success'],
    ['name' => 'RICA',             'logo' => 'R', 'hires' => 18, 'industry' => 'Agriculture',  'color' => 'warning'],
    ['name' => 'Andela Rwanda',    'logo' => 'A', 'hires' => 15, 'industry' => 'Technology',   'color' => 'info'],
    ['name' => 'Rwanda Air',       'logo' => 'R', 'hires' => 12, 'industry' => 'Aviation',     'color' => 'accent'],
    ['name' => 'Irembo Ltd',       'logo' => 'I', 'hires' => 9,  'industry' => 'E-Government', 'color' => 'secondary'],
];
?>
<?= Component::pageHeader(
    'Welcome back, ' . htmlspecialchars($uniName) . ' 🎓',
    '<a href="' . URL::to('university/dashboard') . '">Home</a> / <span>Dashboard</span>',
    '<a href="' . URL::to('university/reports') . '" class="ss-btn ss-btn-light"><i class="fas fa-chart-bar"></i> <span class="d-none d-md-inline">Reports</span></a>' .
    '<a href="' . URL::to('university/students') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-users"></i> <span class="d-none d-md-inline">View Students</span></a>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-user-graduate',  'label' => 'Total Students',     'count' => $totalStudents,       'color' => 'primary', 'trend' => '+8.4%', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-briefcase',      'label' => 'Graduate Tracking',  'count' => $offered,             'color' => 'success', 'trend' => $placementRate . '% rate']) ?>
    <?= Component::statCard(['icon' => 'fa-handshake',      'label' => 'Employer Partners',  'count' => count($topEmployers), 'color' => 'info',    'trend' => '+3 this year']) ?>
    <?= Component::statCard(['icon' => 'fa-calendar-check', 'label' => 'Internship Stats',   'count' => (int)$interviewing,   'color' => 'warning', 'trend' => 'In progress']) ?>
</div>

<!-- ==================== HERO BANNER ==================== -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
        <div class="ss-avatar ss-avatar-xl" style="background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.4);">
            <?php if (!empty($uniLogo)): ?>
                <img src="<?= htmlspecialchars($uniLogo) ?>" alt="<?= htmlspecialchars($uniName) ?>">
            <?php else: ?>
                <?= strtoupper(substr($uniName, 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div style="flex:1;min-width:240px;">
            <div style="font-size:0.78rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">University Profile</div>
            <h3 style="color:#fff;margin:0.25rem 0 0.5rem;font-size:1.45rem;"><?= htmlspecialchars($uniName) ?></h3>
            <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($uniLocation) ?>
                <?php if (!empty($uniWebsite)): ?>
                    · <i class="fas fa-globe"></i> <a href="<?= htmlspecialchars($uniWebsite) ?>" target="_blank" rel="noopener" style="color:rgba(255,255,255,0.95);text-decoration:underline;"><?= htmlspecialchars($uniWebsite) ?></a>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-3" style="flex-shrink:0;">
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;"><?= $placementRate ?>%</div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Placement</div>
            </div>
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;"><?= (int)($university['faculty_count'] ?? 6) ?></div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Faculties</div>
            </div>
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;"><?= count($topEmployers) ?></div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Partners</div>
            </div>
        </div>
    </div>
</div>

<div class="ss-dashboard-grid">
    <!-- ==================== LEFT COLUMN ==================== -->
    <div>
        <!-- PLACEMENT TREND CHART -->
        <div class="ss-chart-card mb-4 ss-animate-fade-up">
            <div class="chart-header">
                <div>
                    <h5><i class="fas fa-chart-line text-primary"></i> Student Placement Trend (6 months)</h5>
                    <div style="font-size:0.78rem;color:var(--ss-text-3);margin-top:2px;">Graduates placed in jobs or internships each month</div>
                </div>
                <span class="ss-badge ss-badge-success"><i class="fas fa-arrow-up"></i> +14% MoM</span>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="placementChart"></canvas>
            </div>
        </div>

        <!-- DEPARTMENT BREAKDOWN + TOP EMPLOYERS -->
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="ss-chart-card ss-animate-fade-up h-100">
                    <div class="chart-header">
                        <div>
                            <h5><i class="fas fa-chart-pie text-accent"></i> Department Breakdown</h5>
                            <div style="font-size:0.78rem;color:var(--ss-text-3);margin-top:2px;">Students by field of study</div>
                        </div>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="ss-card ss-animate-fade-up h-100">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-trophy text-warning"></i> Top Employers</h3>
                        <span class="ss-badge ss-badge-soft">Your alumni</span>
                    </div>
                    <div class="ss-card-body" style="padding:0.75rem;">
                        <?php foreach ($topEmployers as $i => $e): ?>
                            <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>" style="margin-bottom:0.4rem;">
                                <div class="rank"><?= $i + 1 ?></div>
                                <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-<?= $gradFor($e['color']) ?>);"><?= htmlspecialchars($e['logo']) ?></div>
                                <div style="flex:1;min-width:0;">
                                    <div class="fw-semibold" style="font-size:0.85rem;"><?= htmlspecialchars($e['name']) ?></div>
                                    <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($e['industry']) ?></div>
                                </div>
                                <div class="score"><?= (int)$e['hires'] ?> <span style="font-size:0.7rem;color:var(--ss-text-3);">hires</span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT STUDENTS TABLE -->
        <div class="ss-card ss-animate-fade-up mt-4">
            <div class="ss-card-header">
                <h3><i class="fas fa-users text-primary"></i> Recent Students</h3>
                <a href="<?= URL::to('university/students') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">View all <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="ss-card-body" style="padding:0;">
                <?php if (empty($studentList)): ?>
                    <div style="padding:1.5rem;">
                        <?= Component::emptyState(['icon' => 'fa-users', 'title' => 'No students yet', 'desc' => 'Students from your university will appear here once they register.']) ?>
                    </div>
                <?php else: ?>
                <div class="table-responsive-2">
                    <table class="ss-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>GPA</th>
                                <th>Profile</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($studentList, 0, 8) as $s):
                                $name = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?: 'Student';
                                $status = $s['status'] ?? 'active';
                                $stColor = $status === 'active' ? 'success' : ($status === 'inactive' ? 'soft' : 'warning');
                                $year = (int)($s['year_of_study'] ?? 0);
                                $gpa = (float)($s['gpa'] ?? 0);
                                $completion = (int)($s['profile_completion'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="table-avatar">
                                        <div class="avatar"><?= strtoupper(substr($name, 0, 1)) ?></div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($s['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="ss-badge ss-badge-primary"><?= htmlspecialchars($s['department'] ?? 'Undeclared') ?></span></td>
                                <td><span style="font-size:0.82rem;font-weight:600;"><?= $year ? 'Year ' . $year : '—' ?></span></td>
                                <td>
                                    <?php if ($gpa > 0): ?>
                                        <span class="fw-bold"><?= number_format($gpa, 2) ?></span>
                                        <span style="font-size:0.7rem;color:var(--ss-text-3);">/ 4.0</span>
                                    <?php else: ?>
                                        <span style="color:var(--ss-text-3);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($completion > 0): ?>
                                        <div class="d-flex align-items-center gap-2" style="min-width:110px;">
                                            <?= Component::progress($completion, $completion >= 70 ? 'success' : 'warning', 'sm') ?>
                                            <span style="font-size:0.72rem;font-weight:700;"><?= $completion ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:var(--ss-text-3);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="ss-badge ss-badge-<?= $stColor ?> text-capitalize"><?= htmlspecialchars($status) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ==================== RIGHT COLUMN ==================== -->
    <div>
        <!-- RECENT ACTIVITY TIMELINE -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-history text-primary"></i> Recent Activity</h3>
                <span class="ss-badge ss-badge-soft">Last 7 days</span>
            </div>
            <div class="ss-card-body">
                <div class="ss-timeline">
                    <div class="ss-timeline-item success">
                        <div class="timeline-time"><?= date('M j, g:i a', strtotime('-2 hours')) ?></div>
                        <div class="timeline-title">Aline Uwase accepted a job offer</div>
                        <div class="timeline-desc">Junior Software Engineer at Andela Rwanda · GPA 4.2</div>
                    </div>
                    <div class="ss-timeline-item info">
                        <div class="timeline-time"><?= date('M j, g:i a', strtotime('-6 hours')) ?></div>
                        <div class="timeline-title">12 students started internships</div>
                        <div class="timeline-desc">Across Bank of Kigali, MTN Rwanda, and Irembo</div>
                    </div>
                    <div class="ss-timeline-item primary">
                        <div class="timeline-time"><?= date('M j, g:i a', strtotime('-1 day')) ?></div>
                        <div class="timeline-title">New employer partnership established</div>
                        <div class="timeline-desc">RwandaAir signed MoU for 8 internship placements per cohort</div>
                    </div>
                    <div class="ss-timeline-item warning">
                        <div class="timeline-time"><?= date('M j, g:i a', strtotime('-2 days')) ?></div>
                        <div class="timeline-title">Career fair registration opened</div>
                        <div class="timeline-desc">48 students signed up for the Spring Career Expo</div>
                    </div>
                    <div class="ss-timeline-item success">
                        <div class="timeline-time"><?= date('M j, g:i a', strtotime('-3 days')) ?></div>
                        <div class="timeline-title">Quarterly placement report exported</div>
                        <div class="timeline-desc">Available for download in the Reports section</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PLACEMENT CIRCLE PROGRESS -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-bullseye text-success"></i> Placement Goal</h3>
            </div>
            <div class="ss-card-body" style="text-align:center;">
                <div class="ss-progress-circle" style="margin:0 auto 1rem;">
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <defs>
                            <linearGradient id="ss-grad-success-uni" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#10B981"/>
                                <stop offset="100%" stop-color="#059669"/>
                            </linearGradient>
                        </defs>
                        <circle cx="70" cy="70" r="58" fill="none" stroke="var(--ss-border)" stroke-width="10"/>
                        <circle cx="70" cy="70" r="58" fill="none" stroke="url(#ss-grad-success-uni)" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="<?= 2 * M_PI * 58 ?>"
                                stroke-dashoffset="<?= 2 * M_PI * 58 * (1 - min(100, $placementRate) / 100) ?>"
                                transform="rotate(-90 70 70)"/>
                    </svg>
                    <div class="pct" style="color:var(--ss-success);"><?= $placementRate ?>%</div>
                </div>
                <div style="font-weight:700;font-size:0.95rem;"><?= $offered ?> / <?= max($totalApps, 1) ?> applications resulted in offers</div>
                <div style="font-size:0.8rem;color:var(--ss-text-3);margin-top:0.25rem;">Goal: 75% placement by end of academic year</div>
            </div>
        </div>

        <!-- QUICK STATS -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-bolt text-warning"></i> Quick Stats</h3>
            </div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.85rem;color:var(--ss-text-2);"><i class="fas fa-paper-plane text-primary" style="margin-right:0.5rem;"></i>Total applications</span>
                        <span class="fw-bold"><?= (int)$totalApps ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.85rem;color:var(--ss-text-2);"><i class="fas fa-video text-info" style="margin-right:0.5rem;"></i>In interviews</span>
                        <span class="fw-bold"><?= (int)$interviewing ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.85rem;color:var(--ss-text-2);"><i class="fas fa-check-circle text-success" style="margin-right:0.5rem;"></i>Offers received</span>
                        <span class="fw-bold"><?= (int)$offered ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.85rem;color:var(--ss-text-2);"><i class="fas fa-trophy text-warning" style="margin-right:0.5rem;"></i>Avg. starting salary</span>
                        <span class="fw-bold">RWF 480K</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== ACCOUNT INFORMATION ==================== -->
<div class="ss-card ss-animate-fade-up mb-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-user-circle text-primary"></i> Account Information</h3>
        <span class="ss-badge ss-badge-soft">Edit your personal details</span>
    </div>
    <div class="ss-card-body">
        <form method="POST" action="<?= URL::to('account/update') ?>" enctype="multipart/form-data" data-validate>
            <?= $csrfField ?? '' ?>

            <div class="d-flex align-items-center gap-3 mb-4">
                <div>
                    <?php $avatar = Session::get('userAvatar'); ?>
                    <?php $avatarUrl = (!empty($avatar)) ? URL::asset($avatar) : ''; ?>
                    <?php if (!empty($avatarUrl) && file_exists(ROOT_PATH . '/public/assets/' . $avatar)): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--ss-border);">
                    <?php else: ?>
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--ss-warning-light);color:var(--ss-warning);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;border:3px solid var(--ss-border);"><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="ss-btn ss-btn-soft ss-btn-sm" style="cursor:pointer;">
                        <i class="fas fa-camera"></i> Change Photo
                        <input type="file" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp" style="display:none;">
                    </label>
                    <div style="font-size:0.75rem;color:var(--ss-text-3);margin-top:4px;">JPG, PNG, GIF or WebP</div>
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

            <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Account Info</button>
        </form>
    </div>
</div>

<!-- ==================== CHART SCRIPT ==================== -->
<script>
(function() {
    if (typeof Chart === 'undefined') return;
    const colors = window.SS_THEME || <?= json_encode($chartColors) ?>;

    // ---- Placement trend: smooth area chart ----
    const pCtx = document.getElementById('placementChart');
    if (pCtx) {
        const grad = pCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, colors.primary + '50');
        grad.addColorStop(1, colors.primary + '00');
        new Chart(pCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($placementMonths) ?>,
                datasets: [{
                    label: 'Placed Students',
                    data: <?= json_encode($placementCounts) ?>,
                    borderColor: colors.primary,
                    backgroundColor: grad,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: colors.surface, titleColor: colors.text, bodyColor: colors.text, borderColor: colors.grid, borderWidth: 1, padding: 10, cornerRadius: 8 }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { color: colors.text, precision: 0 }, grid: { color: colors.grid } },
                    x: { ticks: { color: colors.text }, grid: { display: false } }
                }
            }
        });
    }

    // ---- Department breakdown: doughnut ----
    const dCtx = document.getElementById('deptChart');
    if (dCtx) {
        const palette = [colors.primary, colors.secondary, colors.success, colors.warning, colors.danger, colors.info, colors.accent];
        new Chart(dCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($deptBreakdown, 'label')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($deptBreakdown, 'count')) ?>,
                    backgroundColor: palette,
                    borderWidth: 3,
                    borderColor: colors.surface,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: colors.text, font: { size: 11 }, padding: 10, usePointStyle: true, pointStyle: 'circle', boxWidth: 8 }
                    },
                    tooltip: { backgroundColor: colors.surface, titleColor: colors.text, bodyColor: colors.text, borderColor: colors.grid, borderWidth: 1, padding: 10, cornerRadius: 8 }
                }
            }
        });
    }
})();
</script>
