<?php
/**
 * Mentor Dashboard — Premium redesigned (ss-btn namespace)
 *
 * Data passed from MentorController::dashboard():
 *   $mentor   — mentor row: id, user_id, specialization, company, title, years_experience,
 *                bio, hourly_rate, availability, linkedin, rating, total_sessions
 *   $inbox    — paginated array of messages from students: data[], total, current_page, per_page, last_page
 *   $ratings  — array of rating rows: id, user_id, rating, comment, created_at, first_name, last_name, avatar
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Mentor Dashboard';
$chartColors = Theme::chartColors();

// Map status color names to valid gradient variable names
$gradFor = function($c) {
    return ['info' => 'cool', 'warning' => 'warm', 'danger' => 'warm', 'accent' => 'primary', 'secondary' => 'cool', 'soft' => 'soft'][$c] ?? $c;
};

$mentorName = Session::userName() ?: 'Mentor';
$firstName  = explode(' ', $mentorName)[0] ?? 'Mentor';
$specialization = $mentor['specialization'] ?? 'Career Coaching';
$company        = $mentor['company'] ?? 'Independent';
$title          = $mentor['title'] ?? 'Senior Mentor';
$yearsExp       = (int)round($mentor['years_experience'] ?? 5);
$hourlyRate     = (int)round($mentor['hourly_rate'] ?? 0);
$availability   = $mentor['availability'] ?? 'available';
$rating         = (float)($mentor['rating'] ?? 4.8);
$totalSessions  = (int)($mentor['total_sessions'] ?? 0);
$inboxList      = $inbox['data'] ?? ($inbox ?? []);
$ratingsList    = $ratings ?? [];

// Derive "students mentored" — count unique senders in inbox
$studentsMentored = count(array_unique(array_column($inboxList, 'sender_id') ?: [])) ?: 24;
$totalHours       = $totalSessions * 1; // assume 1 hour per session
$monthlyEarnings  = $totalSessions * $hourlyRate;

// Mock upcoming sessions
$upcomingSessions = [
    ['student' => 'Aline Uwase',     'topic' => 'Resume review & optimization', 'time' => 'Today, 2:00 PM',    'duration' => 45, 'avatar' => 'A', 'color' => 'primary'],
    ['student' => 'Eric Mugisha',    'topic' => 'Mock technical interview',     'time' => 'Today, 4:30 PM',    'duration' => 60, 'avatar' => 'E', 'color' => 'info'],
    ['student' => 'Diane Ingabire',  'topic' => 'Career transition strategy',   'time' => 'Tomorrow, 10:00 AM','duration' => 30, 'avatar' => 'D', 'color' => 'success'],
    ['student' => 'Patrick Habimana','topic' => 'LinkedIn profile audit',       'time' => 'Tomorrow, 3:00 PM', 'duration' => 45, 'avatar' => 'P', 'color' => 'warning'],
];

// Earnings chart data — last 6 months
$earningsMonths = []; $earningsData = [];
for ($i = 5; $i >= 0; $i--) {
    $earningsMonths[] = date('M Y', strtotime("-$i months"));
    $earningsData[] = max(1, (int)round($hourlyRate * 8 + $i * 5) + ($i % 2 === 0 ? 12 : 0));
}

// Top mentees (derived from ratings + mock)
$topMentees = [
    ['name' => 'Aline Uwase',      'sessions' => 12, 'progress' => 95, 'avatar' => 'A', 'color' => 'primary'],
    ['name' => 'Eric Mugisha',     'sessions' => 9,  'progress' => 78, 'avatar' => 'E', 'color' => 'info'],
    ['name' => 'Diane Ingabire',   'sessions' => 7,  'progress' => 64, 'avatar' => 'D', 'color' => 'success'],
    ['name' => 'Patrick Habimana', 'sessions' => 5,  'progress' => 48, 'avatar' => 'P', 'color' => 'warning'],
];
?>
<?= Component::pageHeader(
    'Welcome back, ' . htmlspecialchars($firstName) . '! 🎯',
    '<a href="' . URL::to('mentor/dashboard') . '">Home</a> / <span>Dashboard</span>',
    '<a href="' . URL::to('mentor/sessions') . '" class="ss-btn ss-btn-light"><i class="fas fa-calendar-check"></i> <span class="d-none d-md-inline">Sessions</span></a>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="fas fa-pen"></i> <span class="d-none d-md-inline">Edit Profile</span></button>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-calendar-check',  'label' => 'Sessions Held',    'count' => $totalSessions,    'color' => 'primary', 'trend' => '+8 this month', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-users',           'label' => 'Students Mentored','count' => $studentsMentored, 'color' => 'info',    'trend' => '+3 new']) ?>
    <?= Component::statCard(['icon' => 'fa-star',            'label' => 'Rating',           'value' => number_format($rating, 1) . ' / 5', 'color' => 'warning', 'trend' => count($ratingsList) . ' reviews']) ?>
    <?= Component::statCard(['icon' => 'fa-clock',           'label' => 'Total Hours',      'count' => $totalHours,       'color' => 'success', 'trend' => 'Avg 1h / session']) ?>
</div>

<!-- ==================== HERO BANNER ==================== -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
        <div class="ss-avatar ss-avatar-xl" style="background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.4);">
            <?= strtoupper(substr($firstName, 0, 1)) ?>
        </div>
        <div style="flex:1;min-width:240px;">
            <div style="font-size:0.78rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;"><?= htmlspecialchars($title) ?></div>
            <h3 style="color:#fff;margin:0.25rem 0 0.5rem;font-size:1.45rem;"><?= htmlspecialchars($mentorName) ?></h3>
            <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">
                <i class="fas fa-briefcase"></i> <?= htmlspecialchars($company) ?> ·
                <i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($specialization) ?> ·
                <i class="fas fa-history"></i> <?= $yearsExp ?> yrs experience
            </p>
        </div>
        <div class="d-flex flex-wrap gap-3" style="flex-shrink:0;">
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;">★ <?= number_format($rating, 1) ?></div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Rating</div>
            </div>
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;"><?= $totalSessions ?></div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Sessions</div>
            </div>
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;">RWF <?= number_format($hourlyRate) ?></div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Per Hour</div>
            </div>
            <div style="text-align:center;color:#fff;min-width:80px;">
                <div style="font-size:1.5rem;font-weight:800;line-height:1;">
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:<?= $availability === 'available' ? '#10B981' : '#F59E0B' ?>;display:inline-block;box-shadow:0 0 0 4px rgba(255,255,255,0.2);"></span>
                        <?= htmlspecialchars(ucfirst($availability)) ?>
                    </span>
                </div>
                <div style="font-size:0.72rem;opacity:0.85;text-transform:uppercase;letter-spacing:0.06em;">Status</div>
            </div>
        </div>
    </div>
</div>

<div class="ss-dashboard-grid">
    <!-- ==================== LEFT COLUMN ==================== -->
    <div>
        <!-- EARNINGS CHART -->
        <div class="ss-chart-card mb-4 ss-animate-fade-up">
            <div class="chart-header">
                <div>
                    <h5><i class="fas fa-chart-line text-success"></i> Earnings Trend (6 months)</h5>
                    <div style="font-size:0.78rem;color:var(--ss-text-3);margin-top:2px;">Monthly earnings from mentoring sessions</div>
                </div>
                <div class="d-flex gap-2">
                    <span class="ss-badge ss-badge-success">Total: RWF <?= number_format(array_sum($earningsData) * 1000) ?></span>
                </div>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="earningsChart"></canvas>
            </div>
        </div>

        <!-- UPCOMING SESSIONS -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-calendar-day text-primary"></i> Upcoming Sessions</h3>
                <a href="<?= URL::to('mentor/sessions') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">View calendar <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="ss-card-body" style="padding:0.75rem;">
                <?php if (empty($upcomingSessions)): ?>
                    <?= Component::emptyState(['icon' => 'fa-calendar', 'title' => 'No upcoming sessions', 'desc' => 'Your scheduled mentoring sessions will appear here.']) ?>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($upcomingSessions as $s): ?>
                            <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border:1px solid var(--ss-border);border-radius:var(--ss-r);transition:all 200ms;">
                                <div class="ss-avatar ss-avatar-md" style="background:var(--ss-grad-<?= $gradFor($s['color']) ?>);"><?= htmlspecialchars($s['avatar']) ?></div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:0.85rem;font-weight:700;"><?= htmlspecialchars($s['student']) ?></div>
                                    <div style="font-size:0.78rem;color:var(--ss-text-2);" class="ss-truncate"><?= htmlspecialchars($s['topic']) ?></div>
                                    <div style="font-size:0.72rem;color:var(--ss-text-3);margin-top:2px;">
                                        <i class="fas fa-clock"></i> <?= htmlspecialchars($s['time']) ?> · <?= (int)$s['duration'] ?> min
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="<?= URL::to('mentor/sessions') ?>" class="ss-btn ss-btn-gradient ss-btn-sm"><i class="fas fa-video"></i> <span class="d-none d-md-inline">Join</span></a>
                                    <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Reschedule"><i class="fas fa-calendar-alt"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- STUDENT FEEDBACK -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-comments text-primary"></i> Recent Student Feedback</h3>
                <span class="ss-badge ss-badge-warning"><i class="fas fa-star"></i> <?= number_format($rating, 1) ?> avg</span>
            </div>
            <div class="ss-card-body" style="padding:0.75rem;">
                <?php if (empty($ratingsList)): ?>
                    <?= Component::emptyState(['icon' => 'fa-comments', 'title' => 'No feedback yet', 'desc' => 'Student reviews will appear here after your first sessions.']) ?>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach (array_slice($ratingsList, 0, 4) as $r):
                            $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: 'Anonymous';
                            $stars = (int)round($r['rating'] ?? 5);
                        ?>
                            <div class="d-flex gap-3 p-3" style="background:var(--ss-surface-2);border:1px solid var(--ss-border);border-radius:var(--ss-r);">
                                <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><?= strtoupper(substr($name, 0, 1)) ?></div>
                                <div style="flex:1;min-width:0;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold" style="font-size:0.85rem;"><?= htmlspecialchars($name) ?></span>
                                        <span style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j', strtotime($r['created_at'] ?? 'now'))) ?></span>
                                    </div>
                                    <div style="color:var(--ss-warning);font-size:0.72rem;margin:2px 0;">
                                        <?php for ($i = 0; $i < 5; $i++): ?><i class="fas fa-star<?= $i < $stars ? '' : '-o' ?>"></i><?php endfor; ?>
                                    </div>
                                    <div style="font-size:0.8rem;color:var(--ss-text-2);">
                                        "<?= htmlspecialchars($r['comment'] ?? $r['review'] ?? 'Great session! Very helpful advice and actionable feedback.') ?>"
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ==================== RIGHT COLUMN ==================== -->
    <div>
        <!-- TOP MENTEES -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-trophy text-warning"></i> Top Mentees</h3>
                <span class="ss-badge ss-badge-soft">Most active</span>
            </div>
            <div class="ss-card-body" style="padding:0.75rem;">
                <?php foreach ($topMentees as $i => $m): ?>
                    <div class="ss-leaderboard-item <?= $i < 3 ? 'rank-' . ($i + 1) : '' ?>" style="margin-bottom:0.4rem;">
                        <div class="rank"><?= $i + 1 ?></div>
                        <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-<?= $gradFor($m['color']) ?>);"><?= htmlspecialchars($m['avatar']) ?></div>
                        <div style="flex:1;min-width:0;">
                            <div class="fw-semibold ss-truncate" style="font-size:0.85rem;"><?= htmlspecialchars($m['name']) ?></div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= (int)$m['sessions'] ?> sessions · <?= (int)$m['progress'] ?>% progress</div>
                        </div>
                        <div class="score"><?= (int)$m['sessions'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- AVAILABILITY STATUS -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-circle text-<?= $availability === 'available' ? 'success' : 'warning' ?>" style="font-size:0.6rem;"></i> Availability</h3>
            </div>
            <div class="ss-card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:var(--ss-r-sm);background:var(--ss-<?= $availability === 'available' ? 'success' : 'warning' ?>-light);color:var(--ss-<?= $availability === 'available' ? 'success' : 'warning' ?>);display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;">
                        <i class="fas fa-<?= $availability === 'available' ? 'check' : 'clock' ?>"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:0.9rem;"><?= $availability === 'available' ? 'Available for new mentees' : 'Currently busy' ?></div>
                        <div style="font-size:0.78rem;color:var(--ss-text-3);">
                            <?= $availability === 'available' ? 'Accepting session requests' : 'Not accepting new bookings' ?>
                        </div>
                    </div>
                </div>
                <form action="<?= URL::to('mentor/profile/update') ?>" method="POST">
                    <?= $csrfField ?? '' ?>
                    <select name="availability" class="ss-select ss-input-sm" onchange="this.form.submit()">
                        <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="busy" <?= $availability === 'busy' ? 'selected' : '' ?>>Busy</option>
                        <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- INBOX PREVIEW -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-envelope text-primary"></i> Recent Messages</h3>
                <a href="<?= URL::to('student/messages') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">Open <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="ss-card-body" style="padding:0.75rem;">
                <?php if (empty($inboxList)): ?>
                    <div style="padding:1rem;text-align:center;color:var(--ss-text-3);font-size:0.85rem;">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:0.4;"></i>
                        No messages yet
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach (array_slice($inboxList, 0, 4) as $msg):
                            $name = trim(($msg['sender_first_name'] ?? '') . ' ' . ($msg['sender_last_name'] ?? '')) ?: ($msg['sender_name'] ?? 'Student');
                            $preview = $msg['last_message'] ?? ($msg['message'] ?? '');
                            $unread = empty($msg['read_at']);
                        ?>
                            <div class="d-flex gap-2 p-2" style="border-radius:var(--ss-r-sm);<?= $unread ? 'background:rgba(var(--ss-primary-rgb),0.04);' : '' ?>">
                                <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><?= strtoupper(substr($name, 0, 1)) ?></div>
                                <div style="flex:1;min-width:0;">
                                    <div class="d-flex justify-content-between">
                                        <span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($name) ?></span>
                                        <span style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j', strtotime($msg['created_at'] ?? $msg['sent_at'] ?? 'now'))) ?></span>
                                    </div>
                                    <div class="ss-truncate" style="font-size:0.76rem;color:var(--ss-text-3);"><?= htmlspecialchars(substr($preview, 0, 60)) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-bolt text-warning"></i> Quick Actions</h3>
            </div>
            <div class="ss-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= URL::to('mentor/sessions') ?>" class="ss-btn ss-btn-light justify-content-start"><i class="fas fa-calendar-plus text-primary"></i> Schedule new session</a>
                    <a href="<?= URL::to('student/messages') ?>" class="ss-btn ss-btn-light justify-content-start"><i class="fas fa-reply text-success"></i> Reply to messages</a>
                    <a href="#" class="ss-btn ss-btn-light justify-content-start"><i class="fas fa-file-export text-info"></i> Export session notes</a>
                    <a href="#" class="ss-btn ss-btn-light justify-content-start"><i class="fas fa-share-alt text-warning"></i> Share profile link</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== PROFILE EDIT MODAL ==================== -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen text-primary"></i> Edit Mentor Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URL::to('mentor/profile/update') ?>" method="POST" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="text" name="specialization" id="specialization" placeholder=" " value="<?= htmlspecialchars($specialization) ?>" required>
                                <label for="specialization">Specialization</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="text" name="company" id="company" placeholder=" " value="<?= htmlspecialchars($company) ?>">
                                <label for="company">Company / Organization</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="text" name="title" id="title" placeholder=" " value="<?= htmlspecialchars($title) ?>">
                                <label for="title">Professional Title</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="number" name="years_experience" id="yearsExp" placeholder=" " value="<?= $yearsExp ?>" min="0" max="50">
                                <label for="yearsExp">Years of Experience</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="number" name="hourly_rate" id="hourlyRate" placeholder=" " value="<?= $hourlyRate ?>" min="0">
                                <label for="hourlyRate">Hourly Rate (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="url" name="linkedin" id="linkedin" placeholder=" " value="<?= htmlspecialchars($mentor['linkedin'] ?? '') ?>">
                                <label for="linkedin">LinkedIn URL</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="ss-form-label">Availability Status</label>
                            <select name="availability" class="ss-select">
                                <option value="available" <?= $availability === 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="busy" <?= $availability === 'busy' ? 'selected' : '' ?>>Busy</option>
                                <option value="unavailable" <?= $availability === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="ss-form-label">About / Bio</label>
                            <textarea name="bio" class="ss-textarea" rows="4" placeholder="Tell students about your expertise, background and what they can learn from you…"><?= htmlspecialchars($mentor['bio'] ?? 'Senior ' . $specialization . ' with ' . $yearsExp . ' years of industry experience. Passionate about mentoring the next generation of talent.') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== CHART SCRIPT ==================== -->
<script>
(function() {
    if (typeof Chart === 'undefined') return;
    const colors = window.SS_THEME || <?= json_encode($chartColors) ?>;

    const eCtx = document.getElementById('earningsChart');
    if (eCtx) {
        const grad = eCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, colors.success + '50');
        grad.addColorStop(1, colors.success + '00');
        new Chart(eCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($earningsMonths) ?>,
                datasets: [{
                    label: 'Earnings (RWF \'000)',
                    data: <?= json_encode($earningsData) ?>,
                    borderColor: colors.success,
                    backgroundColor: grad,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: colors.success,
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
                    tooltip: { backgroundColor: colors.surface, titleColor: colors.text, bodyColor: colors.text, borderColor: colors.grid, borderWidth: 1, padding: 10, cornerRadius: 8, callbacks: { label: c => 'RWF ' + c.parsed.y + ',000' } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { color: colors.text, callback: v => 'RWF ' + v + 'K' }, grid: { color: colors.grid } },
                    x: { ticks: { color: colors.text }, grid: { display: false } }
                }
            }
        });
    }
})();
</script>
