<?php
/**
 * Events — Career events (workshops, job fairs, webinars) (v3)
 *
 * Data passed from InnovationController::events():
 *   $events (array with title, description, start_date, end_date, location, type, max_participants)
 *
 * Register button AJAX-posts to /student/events/{id}/register
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Career Events';

$typeColors = ['workshop' => 'primary', 'job_fair' => 'success', 'webinar' => 'info', 'networking' => 'warning', 'info_session' => 'accent'];
$typeIcons  = ['workshop' => 'fa-tools', 'job_fair' => 'fa-store', 'webinar' => 'fa-video', 'networking' => 'fa-handshake', 'info_session' => 'fa-chalkboard-teacher'];
?>
<?= Component::pageHeader(
    'Career Events',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Events</span>',
    '<div class="ss-tabs-pills">' .
        '<button class="ss-tab-pill active" data-tab="#all-events">All</button>' .
        '<button class="ss-tab-pill" data-tab="#workshops">Workshops</button>' .
        '<button class="ss-tab-pill" data-tab="#fairs">Job Fairs</button>' .
        '<button class="ss-tab-pill" data-tab="#webinars">Webinars</button>' .
    '</div>'
) ?>

<div id="all-events">
    <?php if (!empty($events)): ?>
        <div class="row g-4">
            <?php foreach ($events as $i => $e):
                $type      = $e['type'] ?? 'workshop';
                $color     = $typeColors[$type] ?? 'primary';
                $icon      = $typeIcons[$type] ?? 'fa-calendar';
                $startDate = strtotime($e['start_date'] ?? 'now');
                $day       = date('d', $startDate);
                $month     = date('M', $startDate);
            ?>
                <div class="col-md-6 col-lg-4 ss-animate-fade-up ss-delay-<?= (string)(($i % 5) + 1) ?>">
                    <div class="ss-card ss-hover-lift h-100" style="padding:1.5rem;border-top:4px solid var(--ss-<?= $color ?>);">
                        <div class="d-flex gap-3 mb-3">
                            <div style="width:64px;text-align:center;flex-shrink:0;">
                                <div style="background:var(--ss-surface-2);border-radius:var(--ss-radius);padding:0.5rem;">
                                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:var(--ss-text-3);"><?= htmlspecialchars($month) ?></div>
                                    <div style="font-size:1.75rem;font-weight:900;color:var(--ss-text);line-height:1;"><?= htmlspecialchars($day) ?></div>
                                </div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <?= Component::badge(ucwords(str_replace('_', ' ', $type)), $color, $icon) ?>
                                <h5 style="font-size:0.95rem;margin:0;" class="ss-clamp-2"><?= htmlspecialchars($e['title'] ?? '') ?></h5>
                            </div>
                        </div>
                        <p style="font-size:0.82rem;color:var(--ss-text-2);margin-bottom:0.75rem;" class="ss-clamp-2"><?= htmlspecialchars($e['description'] ?? '') ?></p>
                        <div class="d-flex flex-column gap-1 mb-3" style="font-size:0.78rem;color:var(--ss-text-3);">
                            <div><i class="fas fa-clock me-2"></i> <?= htmlspecialchars(date('g:i A', $startDate)) ?> - <?= htmlspecialchars(date('g:i A', strtotime($e['end_date'] ?? 'now'))) ?></div>
                            <div><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($e['location'] ?? 'TBA') ?></div>
                            <?php if (!empty($e['max_participants'])): ?>
                                <div><i class="fas fa-users me-2"></i> Max <?= (int)$e['max_participants'] ?> participants</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="ss-btn ss-btn-gradient ss-btn-sm flex-fill" onclick="registerEvent(<?= (int)$e['id'] ?>, this)">
                                <i class="fas fa-check"></i> Register
                            </button>
                            <button class="ss-btn ss-btn-light ss-btn-sm" onclick="alert('Details for: <?= htmlspecialchars($e['title'] ?? '') ?>')">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="ss-card">
            <div class="ss-card-body">
                <?= Component::emptyState(['icon' => 'fa-calendar-alt', 'title' => 'No upcoming events', 'desc' => 'Check back soon — new workshops, job fairs, and webinars are added every week.']) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
async function registerEvent(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        const res = await fetch('<?= URL::to('student/events/') ?>' + id + '/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token || '' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Registered';
            btn.classList.remove('ss-btn-gradient');
            btn.classList.add('ss-btn-success');
            window.ssToast && ssToast.show(data.message || 'Registered!', 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Register';
            window.ssToast && ssToast.show(data.message || 'Could not register', 'error');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Register';
        window.ssToast && ssToast.show('Network error', 'error');
    }
}
</script>
