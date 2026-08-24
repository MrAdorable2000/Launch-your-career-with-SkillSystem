<?php
/**
 * Student Dashboard — Premium redesigned
 * Data from StudentController::dashboard():
 * $student, $skills, $stats, $recentApplications, $monthlyData, $recentNotifications,
 * $aiScore, $recommendations, $recInternships, $activities, $badges, $portfolioCount
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Theme;
use App\Helpers\Component;

$firstName = explode(' ', $userName ?? 'Student')[0] ?? 'Student';
$completion = (int)($student['profile_completion'] ?? 0);
$score = $aiScore['score'] ?? 0;
$grade = $aiScore['grade'] ?? 'F';
$skillCount = count($skills ?? []);
$appCount = $stats['total'] ?? 0;
$chartColors = Theme::chartColors();

// Build monthly chart data
$months = []; $monthCounts = [];
foreach ($monthlyData ?? [] as $row) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthCounts[] = (int)$row['count'];
}
if (empty($months)) {
    for ($i = 5; $i >= 0; $i--) { $months[] = date('M Y', strtotime("-$i months")); $monthCounts[] = 0; }
}

$pageTitle = 'Dashboard';
?>
<?= Component::pageHeader(
    'Welcome back, ' . htmlspecialchars($firstName) . '! 👋',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Dashboard</span>',
    '<a href="' . URL::to('student/profile') . '" class="ss-btn ss-btn-light"><i class="fas fa-user"></i> <span class="d-none d-md-inline">View Profile</span></a>' .
    '<a href="' . URL::to('student/jobs') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-search"></i> <span class="d-none d-md-inline">Find Jobs</span></a>'
) ?>

<!-- STAT CARDS -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-folder-open', 'label' => 'Applications', 'count' => $appCount, 'color' => 'primary', 'trend' => '+' . $appCount, 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-code', 'label' => 'Skills', 'count' => $skillCount, 'color' => 'info', 'trend' => 'Updated', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-folder-plus', 'label' => 'Portfolio', 'count' => $portfolioCount, 'color' => 'success', 'trend' => 'Active', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-file-alt', 'label' => 'Resume Score', 'value' => $score . '/100', 'color' => 'warning', 'trend' => 'Grade ' . $grade, 'trendUp' => $score >= 60]) ?>
</div>

<div class="ss-dashboard-grid">
    <!-- LEFT COLUMN -->
    <div>
        <!-- AI Score Banner -->
        <div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
            <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
                <div style="flex-shrink:0;text-align:center;">
                    <div style="font-size:0.78rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;">AI Resume Score</div>
                    <div style="font-size:3.5rem;font-weight:900;color:#fff;line-height:1;margin:0.25rem 0;"><?= $score ?><span style="font-size:1.5rem;opacity:0.7;">/100</span></div>
                    <div style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.3rem 0.85rem;background:rgba(255,255,255,0.2);border-radius:9999px;font-weight:700;font-size:0.82rem;"><i class="fas fa-trophy"></i> Grade <?= htmlspecialchars($grade) ?></div>
                </div>
                <div style="flex:1;min-width:200px;">
                    <h3 style="color:#fff;margin:0 0 0.5rem;font-size:1.2rem;"><?= $score >= 80 ? 'Excellent work!' : ($score >= 60 ? 'Good progress!' : 'Needs improvement') ?></h3>
                    <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">
                        <?php if (!empty($aiScore['suggestions'])): ?>
                            <?= htmlspecialchars($aiScore['suggestions'][0]['text']) ?>
                        <?php else: ?>
                            Your profile is well-optimized. Keep updating it!
                        <?php endif; ?>
                    </p>
                </div>
                <a href="<?= URL::to('student/ai-score') ?>" class="ss-btn ss-btn-light" style="background:rgba(255,255,255,0.2);color:#fff;border:none;">Improve <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-user-check text-primary"></i> Profile Completion</h3>
                <span class="ss-badge ss-badge-primary ss-badge-lg"><?= $completion ?>%</span>
            </div>
            <div class="ss-card-body">
                <?= Component::progress($completion, 'primary', 'lg') ?>
                <div class="row g-3 mt-2">
                    <?php
                    $checks = [
                        ['label' => 'Basic Info', 'done' => !empty($student['bio']), 'icon' => 'fa-id-card'],
                        ['label' => 'Department', 'done' => !empty($student['department']), 'icon' => 'fa-graduation-cap'],
                        ['label' => 'Skills', 'done' => $skillCount > 0, 'icon' => 'fa-code'],
                        ['label' => 'Portfolio', 'done' => $portfolioCount > 0, 'icon' => 'fa-folder-plus'],
                        ['label' => 'Avatar', 'done' => !empty(Session::get('userAvatar')), 'icon' => 'fa-image'],
                        ['label' => 'LinkedIn', 'done' => !empty($student['linkedin']), 'icon' => 'fa-linkedin'],
                    ];
                    foreach ($checks as $c):
                    ?>
                    <div class="col-6 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;<?= $c['done'] ? 'background:var(--ss-success-light);color:var(--ss-success);' : 'background:var(--ss-surface-hover);color:var(--ss-text-3);' ?>">
                                <i class="fas <?= $c['done'] ? 'fa-check' : $c['icon'] ?>"></i>
                            </div>
                            <div>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= $c['label'] ?></div>
                                <div style="font-size:0.78rem;font-weight:600;color:<?= $c['done'] ? 'var(--ss-success)' : 'var(--ss-text-3)' ?>;"><?= $c['done'] ? 'Complete' : 'Pending' ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3"><a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-pen"></i> Complete your profile</a></div>
            </div>
        </div>

        <!-- Recommended Jobs -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-bullseye text-primary"></i> Recommended Jobs for You</h3>
                <a href="<?= URL::to('student/jobs') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">View all <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($recommendations)): ?>
                    <div class="row g-3">
                        <?php foreach (array_slice($recommendations, 0, 4) as $job): ?>
                            <div class="col-md-6">
                                <div class="ss-job-card">
                                    <div class="job-company">
                                        <div class="job-logo"><?= strtoupper(substr($job['company_name'] ?? $job['employer_name'] ?? 'C', 0, 1)) ?></div>
                                        <div style="flex:1;min-width:0;">
                                            <h5 class="ss-clamp-2" style="font-size:0.92rem;"><a href="<?= URL::to('student/jobs/' . $job['id']) ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($job['title']) ?></a></h5>
                                            <div class="company-name"><?= htmlspecialchars($job['company_name'] ?? $job['employer_name'] ?? '') ?></div>
                                        </div>
                                        <span class="ss-badge ss-badge-success"><?= (int)$job['match'] ?>% match</span>
                                    </div>
                                    <div class="job-meta">
                                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location'] ?? 'Remote') ?></span>
                                        <span><i class="fas fa-briefcase"></i> <?= htmlspecialchars(ucfirst($job['type'] ?? 'Full-time')) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-bullseye', 'title' => 'No recommendations yet', 'desc' => 'Add skills and complete your profile to get personalized job recommendations.', 'action' => '<a href="' . URL::to('student/profile') . '" class="ss-btn ss-btn-soft">Complete profile</a>']) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Application Analytics Chart -->
        <div class="ss-chart-card mb-4 ss-animate-fade-up">
            <div class="chart-header">
                <h5><i class="fas fa-chart-line text-primary"></i> Application Activity (6 months)</h5>
                <span class="ss-badge ss-badge-primary">Total: <?= $appCount ?></span>
            </div>
            <div class="chart-canvas-wrap"><canvas id="applicationChart"></canvas></div>
        </div>

        <!-- Skills Progress -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-code text-primary"></i> Your Skills</h3>
                <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">Edit <i class="fas fa-pen"></i></a>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($skills)): ?>
                    <div class="row g-3">
                        <?php foreach (array_slice($skills, 0, 6) as $sk):
                            $levels = ['beginner' => 25, 'intermediate' => 50, 'advanced' => 75, 'expert' => 100];
                            $pct = $levels[$sk['proficiency_level'] ?? 'intermediate'] ?? 50;
                        ?>
                        <div class="col-md-6">
                            <div class="skill-bar">
                                <div class="skill-head"><span class="skill-name"><?= htmlspecialchars($sk['name'] ?? 'Skill') ?></span><span class="skill-level text-capitalize"><?= htmlspecialchars($sk['proficiency_level'] ?? 'intermediate') ?></span></div>
                                <?= Component::progress($pct, 'primary', 'sm') ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-code', 'title' => 'No skills added', 'desc' => 'Add skills to your profile to improve job matching.', 'action' => '<a href="' . URL::to('student/profile') . '" class="ss-btn ss-btn-soft">Add skills</a>']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div>
        <!-- Badges -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-medal text-warning"></i> Achievement Badges</h3>
                <a href="<?= URL::to('student/badges') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($badges)): ?>
                    <?php foreach (array_slice($badges, 0, 4) as $b): ?>
                        <div class="ss-achievement mb-2">
                            <div class="badge-icon"><i class="fas <?= htmlspecialchars($b['icon']) ?>"></i></div>
                            <div><div style="font-size:0.85rem;font-weight:700;"><?= htmlspecialchars($b['name']) ?></div><div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars($b['description']) ?></div></div>
                            <span class="ms-auto ss-badge ss-badge-warning">+<?= (int)$b['points'] ?> pts</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3"><i class="fas fa-medal mb-2 d-block" style="font-size:2rem;color:var(--ss-text-3);opacity:0.4;"></i><div style="font-size:0.85rem;color:var(--ss-text-3);">Complete activities to earn badges</div></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-folder-open text-primary"></i> Recent Applications</h3>
                <a href="<?= URL::to('student/applications') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($recentApplications)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($recentApplications as $app):
                            $statusColors = ['pending' => 'warning', 'reviewing' => 'info', 'shortlisted' => 'info', 'offered' => 'success', 'accepted' => 'success', 'rejected' => 'danger', 'interview' => 'info'];
                            $sc = $statusColors[$app['status'] ?? 'pending'] ?? 'soft';
                            $title = $app['position_title'] ?? $app['job_title'] ?? $app['title'] ?? 'Application';
                        ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><?= strtoupper(substr($title, 0, 1)) ?></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.85rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($title) ?></div>
                                <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, Y', strtotime($app['applied_at']))) ?></div>
                            </div>
                            <span class="ss-badge ss-badge-<?= $sc ?> text-capitalize"><?= htmlspecialchars($app['status'] ?? 'pending') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-folder-open', 'title' => 'No applications yet', 'desc' => 'Browse jobs and start applying!', 'action' => '<a href="' . URL::to('student/jobs') . '" class="ss-btn ss-btn-soft ss-btn-sm">Browse jobs</a>']) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendar -->
        <div class="ss-card mb-4 ss-animate-fade-up">
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

        <!-- Activity Timeline -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-history text-primary"></i> Activity Timeline</h3></div>
            <div class="ss-card-body">
                <?php if (!empty($activities)): ?>
                    <div class="ss-timeline">
                        <?php foreach ($activities as $a):
                            $colors = ['profile' => 'info', 'application' => 'primary', 'portfolio' => 'success', 'settings' => 'warning', 'login' => 'success'];
                            $color = $colors[$a['type'] ?? 'info'] ?? 'info';
                        ?>
                        <div class="ss-timeline-item <?= $color ?>">
                            <div class="timeline-time"><?= htmlspecialchars(date('M j, g:i a', strtotime($a['created_at']))) ?></div>
                            <div class="timeline-title"><?= htmlspecialchars(ucfirst($a['type'] ?? 'Activity')) ?></div>
                            <div class="timeline-desc"><?= htmlspecialchars($a['description'] ?? '') ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3"><i class="fas fa-history mb-2 d-block" style="font-size:1.5rem;color:var(--ss-text-3);opacity:0.4;"></i><div style="font-size:0.85rem;color:var(--ss-text-3);">No recent activity</div></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-bell text-primary"></i> Notifications</h3><span class="ss-badge ss-badge-primary"><?= count($recentNotifications ?? []) ?> new</span></div>
            <div class="ss-card-body">
                <?php if (!empty($recentNotifications)): ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach (array_slice($recentNotifications, 0, 5) as $n): ?>
                        <div class="d-flex gap-2 p-2 <?= empty($n['read_at']) ? 'rounded' : '' ?>" style="<?= empty($n['read_at']) ? 'background:rgba(var(--ss-primary-rgb),0.04);' : '' ?>">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;"><i class="fas fa-bell"></i></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($n['title']) ?></div>
                                <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars($n['message']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3"><i class="fas fa-bell-slash mb-2 d-block" style="font-size:1.5rem;color:var(--ss-text-3);opacity:0.4;"></i><div style="font-size:0.85rem;color:var(--ss-text-3);">All caught up!</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const ctx = document.getElementById('applicationChart');
    if (!ctx || typeof Chart === 'undefined') return;
    const colors = <?= json_encode($chartColors) ?>;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Applications',
                data: <?= json_encode($monthCounts) ?>,
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                borderWidth: 3, tension: 0.4, fill: true,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7
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
})();
</script>
