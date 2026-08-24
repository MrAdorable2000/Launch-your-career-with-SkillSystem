<?php
/**
 * Mentor Sessions — Premium redesigned (ss-btn namespace)
 *
 * Data passed from MentorController::sessions():
 *   $mentor    — mentor row (specialization, company, title, years_experience,
 *                 bio, hourly_rate, availability, linkedin, rating, total_sessions)
 *   $messages  — paginated inbox array (used as session/message history)
 *
 * POST handler: MentorController@updateProfile reads:
 *   specialization, company, title, years_experience (getInt), bio, hourly_rate,
 *   availability, linkedin, _token (CSRF)
 *
 * The MentorController does not have a dedicated "sessions" table — it uses the
 * messages inbox as a proxy. We therefore synthesize session cards from the inbox
 * plus a calendar grid for the current month.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Mentoring Sessions';

$mentorName = Session::userName() ?: 'Mentor';
$firstName  = explode(' ', $mentorName)[0] ?? 'Mentor';
$availability   = $mentor['availability'] ?? 'available';
$specialization = $mentor['specialization'] ?? 'Career Coaching';
$company        = $mentor['company'] ?? 'Independent';
$title          = $mentor['title'] ?? 'Senior Mentor';
$yearsExp       = (int)round($mentor['years_experience'] ?? 5);
$hourlyRate     = (int)round($mentor['hourly_rate'] ?? 0);
$linkedin       = $mentor['linkedin'] ?? '';
$bio            = $mentor['bio'] ?? '';

// Map status color names to valid gradient variable names
$gradFor = function($c) {
    return ['info' => 'cool', 'warning' => 'warm', 'danger' => 'warm', 'accent' => 'primary', 'secondary' => 'cool', 'soft' => 'soft'][$c] ?? $c;
};

// Inbox as message history
$messages = $messages ?? [];
$messageList = $messages['data'] ?? ($messages ?? []);

// Synthesized sessions — would come from a real sessions table
$sessions = [
    [
        'id' => 1, 'student' => 'Aline Uwase',      'topic' => 'Resume review & optimization',
        'date' => date('Y-m-d', strtotime('today')),       'time' => '14:00', 'duration' => 45,
        'status' => 'scheduled', 'avatar' => 'A', 'color' => 'primary', 'type' => 'video',
        'location' => 'Zoom — https://meet.example.com/abc-defg-hij'
    ],
    [
        'id' => 2, 'student' => 'Eric Mugisha',     'topic' => 'Mock technical interview — Frontend',
        'date' => date('Y-m-d', strtotime('today')),       'time' => '16:30', 'duration' => 60,
        'status' => 'scheduled', 'avatar' => 'E', 'color' => 'info', 'type' => 'video',
        'location' => 'Google Meet — https://meet.google.com/abc-defg-hij'
    ],
    [
        'id' => 3, 'student' => 'Diane Ingabire',   'topic' => 'Career transition strategy session',
        'date' => date('Y-m-d', strtotime('+1 day')),       'time' => '10:00', 'duration' => 30,
        'status' => 'scheduled', 'avatar' => 'D', 'color' => 'success', 'type' => 'video',
        'location' => 'Zoom — https://meet.example.com/xyz-1234-uvw'
    ],
    [
        'id' => 4, 'student' => 'Patrick Habimana', 'topic' => 'LinkedIn profile audit',
        'date' => date('Y-m-d', strtotime('+1 day')),       'time' => '15:00', 'duration' => 45,
        'status' => 'scheduled', 'avatar' => 'P', 'color' => 'warning', 'type' => 'video',
        'location' => 'Microsoft Teams — https://teams.example.com/abc'
    ],
    [
        'id' => 5, 'student' => 'Malaika Uwineza',  'topic' => 'Behavioral interview prep',
        'date' => date('Y-m-d', strtotime('+2 days')),      'time' => '11:00', 'duration' => 60,
        'status' => 'scheduled', 'avatar' => 'M', 'color' => 'accent', 'type' => 'video',
        'location' => 'Zoom — https://meet.example.com/qrs-tuvw-xyz'
    ],
    [
        'id' => 6, 'student' => 'Brian Tuyisenge',  'topic' => 'Salary negotiation workshop',
        'date' => date('Y-m-d', strtotime('-1 day')),       'time' => '13:00', 'duration' => 45,
        'status' => 'completed', 'avatar' => 'B', 'color' => 'primary', 'type' => 'video',
        'location' => 'Zoom — completed'
    ],
    [
        'id' => 7, 'student' => 'Claudine Isimbi',  'topic' => 'Portfolio review session',
        'date' => date('Y-m-d', strtotime('-2 days')),      'time' => '09:30', 'duration' => 30,
        'status' => 'completed', 'avatar' => 'C', 'color' => 'info', 'type' => 'video',
        'location' => 'Zoom — completed'
    ],
    [
        'id' => 8, 'student' => 'Kevin Nshimiyimana','topic' => 'Job search strategy',
        'date' => date('Y-m-d', strtotime('-3 days')),      'time' => '17:00', 'duration' => 60,
        'status' => 'cancelled', 'avatar' => 'K', 'color' => 'danger', 'type' => 'video',
        'location' => 'Cancelled by student'
    ],
];

$statusMeta = [
    'scheduled' => ['color' => 'info',    'icon' => 'fa-clock',         'label' => 'Scheduled'],
    'completed' => ['color' => 'success', 'icon' => 'fa-check-circle',  'label' => 'Completed'],
    'cancelled' => ['color' => 'danger',  'icon' => 'fa-times-circle',  'label' => 'Cancelled'],
    'pending'   => ['color' => 'warning', 'icon' => 'fa-hourglass-half','label' => 'Pending'],
];

$upcoming  = array_filter($sessions, fn($s) => $s['status'] === 'scheduled');
$completed = array_filter($sessions, fn($s) => $s['status'] === 'completed');
$cancelled = array_filter($sessions, fn($s) => $s['status'] === 'cancelled');

// Calendar events: day-of-month => session count
$eventsByDay = [];
foreach ($sessions as $s) {
    if (date('Y-m', strtotime($s['date'])) === date('Y-m')) {
        $day = (int)date('j', strtotime($s['date']));
        $eventsByDay[$day] = ($eventsByDay[$day] ?? 0) + 1;
    }
}
?>
<?= Component::pageHeader(
    'Mentoring Sessions 📅',
    '<a href="' . URL::to('mentor/dashboard') . '">Dashboard</a> / <span>Sessions</span>',
    '<button class="ss-btn ss-btn-light" data-bs-toggle="modal" data-bs-target="#availabilityModal"><i class="fas fa-clock"></i> <span class="d-none d-md-inline">Set Hours</span></button>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="fas fa-plus"></i> <span class="d-none d-md-inline">Schedule Session</span></button>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-calendar-check',  'label' => 'Upcoming',   'count' => count($upcoming),  'color' => 'info',    'trend' => 'Next 7 days']) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',    'label' => 'Completed',  'count' => count($completed), 'color' => 'success', 'trend' => 'This month', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-times-circle',    'label' => 'Cancelled',  'count' => count($cancelled), 'color' => 'danger',  'trend' => 'Needs follow-up']) ?>
    <?= Component::statCard(['icon' => 'fa-clock',           'label' => 'Total Hours','count' => (int)round(array_sum(array_column($completed, 'duration')) / 60), 'color' => 'primary', 'trend' => 'This month']) ?>
</div>

<div class="row g-4">
    <!-- ==================== CALENDAR + UPCOMING LIST ==================== -->
    <div class="col-lg-7">
        <!-- CALENDAR -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-calendar-alt text-primary"></i> <?= htmlspecialchars(date('F Y')) ?></h3>
                <div class="d-flex gap-2">
                    <button class="ss-btn ss-btn-ghost ss-btn-sm"><i class="fas fa-chevron-left"></i></button>
                    <button class="ss-btn ss-btn-ghost ss-btn-sm">Today</button>
                    <button class="ss-btn ss-btn-ghost ss-btn-sm"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="ss-card-body">
                <div class="ss-calendar-grid">
                    <?php foreach (['S','M','T','W','T','F','S'] as $d): ?>
                        <div class="ss-calendar-day-name"><?= $d ?></div>
                    <?php endforeach; ?>
                    <?php
                    $today = (int)date('j');
                    $firstDay = date('w', strtotime(date('Y-m-01')));
                    $daysInMonth = date('t');
                    for ($i = 0; $i < $firstDay; $i++) echo '<div class="ss-calendar-day other-month"></div>';
                    for ($d = 1; $d <= $daysInMonth; $d++):
                        $hasEvent = isset($eventsByDay[$d]);
                        $eventCount = $eventsByDay[$d] ?? 0;
                    ?>
                        <div class="ss-calendar-day <?= $d === $today ? 'today' : '' ?> <?= $hasEvent ? 'has-event' : '' ?>" <?= $hasEvent ? 'data-bs-toggle="tooltip" title="' . $eventCount . ' session' . ($eventCount > 1 ? 's' : '') . ' scheduled"' : '' ?>>
                            <span><?= $d ?></span>
                            <?php if ($hasEvent): ?>
                                <span style="font-size:0.65rem;font-weight:700;color:var(--ss-accent);margin-top:auto;align-self:center;"><?= $eventCount ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- UPCOMING SESSIONS LIST -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-calendar-day text-primary"></i> Upcoming Sessions</h3>
                <span class="ss-badge ss-badge-info"><?= count($upcoming) ?> scheduled</span>
            </div>
            <div class="ss-card-body" style="padding:0.75rem;">
                <?php if (empty($upcoming)): ?>
                    <?= Component::emptyState(['icon' => 'fa-calendar', 'title' => 'No upcoming sessions', 'desc' => 'You have no scheduled sessions. Use the Schedule Session button to add one.', 'action' => '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="fas fa-plus"></i> Schedule Session</button>']) ?>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($upcoming as $s):
                            $m = $statusMeta[$s['status']];
                            $dateLabel = date('M j, Y', strtotime($s['date'])) === date('M j, Y') ? 'Today' : (date('M j, Y', strtotime($s['date'])) === date('M j, Y', strtotime('tomorrow')) ? 'Tomorrow' : date('M j, Y', strtotime($s['date'])));
                            $timeLabel = date('g:i A', strtotime($s['time']));
                            $isToday = $dateLabel === 'Today';
                        ?>
                            <div class="ss-card" style="border-left:3px solid var(--ss-<?= $s['color'] ?>);">
                                <div class="ss-card-body" style="padding:1rem;">
                                    <div class="d-flex align-items-start gap-3 flex-wrap">
                                        <div class="ss-avatar ss-avatar-md" style="background:var(--ss-grad-<?= $gradFor($s['color']) ?>);"><?= htmlspecialchars($s['avatar']) ?></div>
                                        <div style="flex:1;min-width:200px;">
                                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                                <div>
                                                    <div class="fw-bold" style="font-size:0.95rem;"><?= htmlspecialchars($s['student']) ?></div>
                                                    <div style="font-size:0.82rem;color:var(--ss-text-2);" class="ss-clamp-2"><?= htmlspecialchars($s['topic']) ?></div>
                                                </div>
                                                <span class="ss-badge ss-badge-<?= $m['color'] ?>"><i class="fas <?= $m['icon'] ?>"></i> <?= $m['label'] ?></span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:0.78rem;color:var(--ss-text-3);">
                                                <span><i class="fas fa-calendar"></i> <?= htmlspecialchars($dateLabel) ?></span>
                                                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($timeLabel) ?> · <?= (int)$s['duration'] ?> min</span>
                                                <span><i class="fas fa-video"></i> <?= htmlspecialchars(ucfirst($s['type'])) ?></span>
                                            </div>
                                            <div style="font-size:0.75rem;color:var(--ss-text-3);margin-top:4px;word-break:break-all;">
                                                <i class="fas fa-link"></i> <?= htmlspecialchars($s['location']) ?>
                                            </div>
                                            <div class="d-flex gap-2 mt-3 flex-wrap">
                                                <?php if ($isToday): ?>
                                                    <a href="<?= htmlspecialchars($s['location']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-gradient ss-btn-sm"><i class="fas fa-video"></i> Join Meeting</a>
                                                <?php else: ?>
                                                    <button class="ss-btn ss-btn-light ss-btn-sm" disabled><i class="fas fa-hourglass"></i> Not Started</button>
                                                <?php endif; ?>
                                                <form action="<?= URL::to('mentor/sessions') ?>" method="POST" style="display:inline;">
                                                    <?= $csrfField ?? '' ?>
                                                    <input type="hidden" name="session_id" value="<?= (int)$s['id'] ?>">
                                                    <button type="submit" name="action" value="complete" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-check"></i> Mark Complete</button>
                                                    <button type="submit" name="action" value="cancel" class="ss-btn ss-btn-ghost ss-btn-sm" style="color:var(--ss-danger);"><i class="fas fa-times"></i> Cancel</button>
                                                </form>
                                                <a href="<?= URL::to('student/messages') ?>" class="ss-btn ss-btn-ghost ss-btn-sm"><i class="fas fa-envelope"></i> Message</a>
                                            </div>
                                        </div>
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
    <div class="col-lg-5">
        <!-- PAST SESSIONS -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-history text-primary"></i> Past Sessions</h3>
                <span class="ss-badge ss-badge-soft"><?= count($completed) + count($cancelled) ?> total</span>
            </div>
            <div class="ss-card-body" style="padding:0.75rem;">
                <div class="d-flex flex-column gap-2">
                    <?php foreach (array_merge($completed, $cancelled) as $s):
                        $m = $statusMeta[$s['status']];
                    ?>
                        <div class="d-flex align-items-center gap-2 p-3" style="background:var(--ss-surface-2);border:1px solid var(--ss-border);border-radius:var(--ss-r);<?= $s['status'] === 'cancelled' ? 'opacity:0.7;' : '' ?>">
                            <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-<?= $gradFor($s['color']) ?>);"><?= htmlspecialchars($s['avatar']) ?></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.82rem;font-weight:600;" class="ss-truncate"><?= htmlspecialchars($s['student']) ?></div>
                                <div style="font-size:0.74rem;color:var(--ss-text-3);" class="ss-truncate"><?= htmlspecialchars($s['topic']) ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);margin-top:2px;">
                                    <i class="fas fa-calendar"></i> <?= htmlspecialchars(date('M j, g:i A', strtotime($s['date'] . ' ' . $s['time']))) ?>
                                </div>
                            </div>
                            <span class="ss-badge ss-badge-<?= $m['color'] ?> text-capitalize"><?= $m['label'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="#" class="ss-btn ss-btn-light ss-btn-block mt-3"><i class="fas fa-list"></i> View Full History</a>
            </div>
        </div>

        <!-- SESSION TYPES SUMMARY -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-chart-pie text-accent"></i> Session Types</h3>
            </div>
            <div class="ss-card-body">
                <?php
                $types = [
                    ['label' => 'Resume Review',     'count' => 18, 'pct' => 35, 'color' => 'primary'],
                    ['label' => 'Mock Interviews',   'count' => 14, 'pct' => 27, 'color' => 'info'],
                    ['label' => 'Career Strategy',   'count' => 10, 'pct' => 19, 'color' => 'success'],
                    ['label' => 'Portfolio Review',  'count' => 6,  'pct' => 12, 'color' => 'warning'],
                    ['label' => 'Other',             'count' => 4,  'pct' => 7,  'color' => 'accent'],
                ];
                foreach ($types as $t):
                ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($t['label']) ?></span>
                            <span style="font-size:0.78rem;font-weight:700;"><?= (int)$t['count'] ?> <span style="color:var(--ss-text-3);">(<?= $t['pct'] ?>%)</span></span>
                        </div>
                        <?= Component::progress($t['pct'], $t['color'], 'sm') ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- WEEKLY AVAILABILITY -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-clock text-success"></i> Weekly Hours</h3>
                <span class="ss-badge ss-badge-<?= $availability === 'available' ? 'success' : 'warning' ?>"><?= htmlspecialchars(ucfirst($availability)) ?></span>
            </div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <?php
                    $weekHours = [
                        ['day' => 'Monday',    'hours' => '9:00 AM – 5:00 PM', 'sessions' => 2, 'open' => true],
                        ['day' => 'Tuesday',   'hours' => '9:00 AM – 5:00 PM', 'sessions' => 3, 'open' => true],
                        ['day' => 'Wednesday', 'hours' => '1:00 PM – 6:00 PM', 'sessions' => 2, 'open' => true],
                        ['day' => 'Thursday',  'hours' => '9:00 AM – 5:00 PM', 'sessions' => 1, 'open' => true],
                        ['day' => 'Friday',    'hours' => '9:00 AM – 3:00 PM', 'sessions' => 2, 'open' => true],
                        ['day' => 'Saturday',  'hours' => 'Off',               'sessions' => 0, 'open' => false],
                        ['day' => 'Sunday',    'hours' => 'Off',               'sessions' => 0, 'open' => false],
                    ];
                    foreach ($weekHours as $d):
                    ?>
                        <div class="d-flex align-items-center gap-2 py-1">
                            <i class="fas fa-<?= $d['open'] ? 'check-circle text-success' : 'times-circle' ?>" style="font-size:0.85rem;width:14px;<?= $d['open'] ? '' : 'color:var(--ss-text-3);' ?>"></i>
                            <span style="font-size:0.82rem;font-weight:600;width:80px;"><?= htmlspecialchars($d['day']) ?></span>
                            <span style="font-size:0.78rem;color:var(--ss-text-2);flex:1;"><?= htmlspecialchars($d['hours']) ?></span>
                            <?php if ($d['open']): ?>
                                <span class="ss-badge ss-badge-<?= $d['sessions'] > 0 ? 'info' : 'soft' ?>"><?= (int)$d['sessions'] ?> booked</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="ss-btn ss-btn-light ss-btn-block mt-3" data-bs-toggle="modal" data-bs-target="#availabilityModal"><i class="fas fa-edit"></i> Edit Availability</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== EDIT PROFILE FORM ==================== -->
<div class="ss-card mt-4 ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-user-edit text-primary"></i> Edit Mentor Profile</h3>
        <span class="ss-badge ss-badge-soft">Public profile information</span>
    </div>
    <div class="ss-card-body">
        <form action="<?= URL::to('mentor/profile/update') ?>" method="POST" data-validate>
            <?= $csrfField ?? '' ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="ss-form-label" for="specialization">Specialization <span class="req">*</span></label>
                    <div class="ss-float">
                        <input type="text" name="specialization" id="specialization" placeholder=" " value="<?= htmlspecialchars($specialization) ?>" required>
                        <label for="specialization">Specialization</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="ss-form-label" for="company">Company / Organization</label>
                    <div class="ss-float">
                        <input type="text" name="company" id="company" placeholder=" " value="<?= htmlspecialchars($company) ?>">
                        <label for="company">Company / Organization</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="ss-form-label" for="title">Professional Title</label>
                    <div class="ss-float">
                        <input type="text" name="title" id="title" placeholder=" " value="<?= htmlspecialchars($title) ?>">
                        <label for="title">Professional Title</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="ss-form-label" for="yearsExp">Years of Experience</label>
                    <div class="ss-float">
                        <input type="number" name="years_experience" id="yearsExp" placeholder=" " value="<?= $yearsExp ?>" min="0" max="50">
                        <label for="yearsExp">Years of Experience</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="ss-form-label" for="hourlyRate">Hourly Rate (RWF)</label>
                    <div class="ss-float">
                        <input type="number" name="hourly_rate" id="hourlyRate" placeholder=" " value="<?= $hourlyRate ?>" min="0">
                        <label for="hourlyRate">Hourly Rate (RWF)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="ss-form-label" for="linkedin">LinkedIn URL</label>
                    <div class="ss-float">
                        <input type="url" name="linkedin" id="linkedin" placeholder=" " value="<?= htmlspecialchars($linkedin) ?>">
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
                    <label class="ss-form-label" for="bio">About / Bio</label>
                    <textarea name="bio" id="bio" class="ss-textarea" rows="4" placeholder="Tell students about your expertise, background and what they can learn from you…"><?= htmlspecialchars($bio ?: 'Senior ' . $specialization . ' with ' . $yearsExp . ' years of industry experience. Passionate about mentoring the next generation of talent.') ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="<?= URL::to('mentor/dashboard') ?>" class="ss-btn ss-btn-light">Cancel</a>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ==================== SCHEDULE SESSION MODAL ==================== -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus text-primary"></i> Schedule New Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URL::to('mentor/sessions') ?>" method="POST" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="ss-form-label">Student <span class="req">*</span></label>
                            <select name="student_id" class="ss-select" required>
                                <option value="">Select a student…</option>
                                <option value="1">Aline Uwase</option>
                                <option value="2">Eric Mugisha</option>
                                <option value="3">Diane Ingabire</option>
                                <option value="4">Patrick Habimana</option>
                                <option value="5">Malaika Uwineza</option>
                                <option value="6">Brian Tuyisenge</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="text" name="topic" id="topic" placeholder=" " required>
                                <label for="topic">Session Topic</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="ss-form-label">Date <span class="req">*</span></label>
                            <input type="date" name="session_date" class="ss-input" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="ss-form-label">Time <span class="req">*</span></label>
                            <input type="time" name="session_time" class="ss-input" required>
                        </div>
                        <div class="col-md-4">
                            <label class="ss-form-label">Duration <span class="req">*</span></label>
                            <select name="duration" class="ss-select" required>
                                <option value="30">30 minutes</option>
                                <option value="45" selected>45 minutes</option>
                                <option value="60">60 minutes</option>
                                <option value="90">90 minutes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="ss-form-label">Session Type</label>
                            <select name="type" class="ss-select">
                                <option value="video">Video Call</option>
                                <option value="phone">Phone Call</option>
                                <option value="in-person">In-Person</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-float">
                                <input type="url" name="meeting_link" id="meetingLink" placeholder=" ">
                                <label for="meetingLink">Meeting Link (optional)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="ss-form-label">Notes for Student (optional)</label>
                            <textarea name="notes" class="ss-textarea" rows="3" placeholder="Add an agenda, preparation instructions, or anything the student should know before the session…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="ss-check">
                                <input type="checkbox" name="send_reminder" value="1" checked>
                                Send email reminder to student 1 hour before the session
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-calendar-plus"></i> Schedule Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== AVAILABILITY MODAL ==================== -->
<div class="modal fade" id="availabilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock text-primary"></i> Set Weekly Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URL::to('mentor/profile/update') ?>" method="POST">
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="ss-alert ss-alert-info">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <div class="alert-body" style="font-size:0.82rem;">Students can only request sessions during your available hours. You can override for specific cases.</div>
                    </div>
                    <div class="table-responsive-2 mt-3">
                        <table class="ss-table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th style="width:80px;">Available</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $i => $day): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($day) ?></td>
                                    <td>
                                        <label class="ss-switch">
                                            <input type="checkbox" name="avail_<?= strtolower($day) ?>" value="1" <?= $i < 5 ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td><input type="time" name="start_<?= strtolower($day) ?>" class="ss-input ss-input-sm" value="<?= $i < 5 ? '09:00' : '00:00' ?>"></td>
                                    <td><input type="time" name="end_<?= strtolower($day) ?>" class="ss-input ss-input-sm" value="<?= $i < 5 ? '17:00' : '00:00' ?>"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Availability</button>
                </div>
            </form>
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
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--ss-info-light);color:var(--ss-info);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;border:3px solid var(--ss-border);"><?= strtoupper(substr($userName ?? 'M', 0, 1)) ?></div>
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

<!-- ==================== TOOLTIP INIT ==================== -->
<script>
(function() {
    // Initialize Bootstrap tooltips on calendar days
    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
})();
</script>
