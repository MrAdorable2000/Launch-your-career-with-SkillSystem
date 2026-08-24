<?php
/**
 * Employer — Applicants for a Job (premium redesign v3)
 *
 * Data passed from EmployerController::applicants($jobId):
 *   $job        — job row (id, title, type, location, deadline, salary_min, salary_max, remote)
 *   $applicants — array, each row: id, first_name, last_name, email, uni_name,
 *                 department, applied_at, status, user_id
 *
 * Status updates go via POST /employer/applications/{id}/status (JSON endpoint).
 * Controller reads: status (one of pending/reviewing/shortlisted/interview/offered/rejected), _token
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Applicants';

$applicants   = $applicants ?? [];
$job          = $job ?? [];
$appCount     = count($applicants);
$jobTitle     = $job['title'] ?? 'Untitled role';

// Status metadata
$statusMeta = [
    'pending'     => ['color' => 'warning', 'icon' => 'fa-clock',           'label' => 'Pending'],
    'reviewing'   => ['color' => 'info',    'icon' => 'fa-search',          'label' => 'Reviewing'],
    'shortlisted' => ['color' => 'primary', 'icon' => 'fa-star',            'label' => 'Shortlisted'],
    'interview'   => ['color' => 'info',    'icon' => 'fa-video',           'label' => 'Interview'],
    'offered'     => ['color' => 'success', 'icon' => 'fa-check-circle',    'label' => 'Offered'],
    'rejected'    => ['color' => 'danger',  'icon' => 'fa-times-circle',    'label' => 'Rejected'],
];
$renderStatusBadge = function(string $status) use ($statusMeta): string {
    $m = $statusMeta[$status] ?? ['color' => 'soft', 'icon' => 'fa-circle', 'label' => ucfirst($status)];
    return '<span class="ss-badge ss-badge-' . $m['color'] . '"><i class="fas ' . $m['icon'] . '"></i> ' . htmlspecialchars($m['label']) . '</span>';
};

// Counts per status for tabs
$counts = ['all' => $appCount];
foreach (['pending' => 0, 'reviewing' => 0, 'shortlisted' => 0, 'interview' => 0, 'offered' => 0, 'rejected' => 0] as $k => $_) {
    $counts[$k] = 0;
}
foreach ($applicants as $a) {
    $s = $a['status'] ?? 'pending';
    if (isset($counts[$s])) $counts[$s]++;
}

$fullName  = fn($a) => trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
$initials  = fn($a) => strtoupper(substr($a['first_name'] ?? 'U', 0, 1) . substr($a['last_name'] ?? '', 0, 1));

// Salary string for the hero card
$salaryStr = '';
if (!empty($job['salary_min'])) {
    $salaryStr = number_format($job['salary_min']);
    if (!empty($job['salary_max'])) $salaryStr .= ' – ' . number_format($job['salary_max']);
    $salaryStr .= ' RWF';
}
?>
<?= Component::pageHeader(
    'Applicants',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <a href="' . URL::to('employer/jobs') . '">My Jobs</a> / <span>Applicants</span>',
    '<a href="' . URL::to('employer/jobs') . '" class="ss-btn ss-btn-light"><i class="fas fa-arrow-left"></i> <span class="d-none d-md-inline">Back to Jobs</span></a>' .
    '<button type="button" class="ss-btn ss-btn-gradient" onclick="window.print()"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>'
) ?>

<!-- ============== JOB SUMMARY HERO ============== -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
        <div class="ss-avatar ss-avatar-xl" style="background:rgba(255,255,255,0.2);color:#fff;border:3px solid rgba(255,255,255,0.35);flex-shrink:0;">
            <?= strtoupper(substr($jobTitle, 0, 1)) ?>
        </div>
        <div style="flex:1;min-width:240px;">
            <div style="font-size:0.78rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">
                <?= htmlspecialchars(ucfirst($job['type'] ?? 'full-time')) ?> · Job ID #<?= (int)($job['id'] ?? 0) ?>
            </div>
            <h3 style="color:#fff;margin:0.2rem 0 0.5rem;font-size:1.4rem;"><?= htmlspecialchars($jobTitle) ?></h3>
            <div class="d-flex flex-wrap gap-3" style="font-size:0.85rem;color:rgba(255,255,255,0.9);">
                <span><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($job['location'] ?? 'Remote') ?></span>
                <?php if (!empty($job['remote'])): ?>
                <span><i class="fas fa-wifi me-1"></i> Remote OK</span>
                <?php endif; ?>
                <?php if ($salaryStr): ?>
                <span><i class="fas fa-money-bill-wave me-1"></i> <?= $salaryStr ?></span>
                <?php endif; ?>
                <?php if (!empty($job['deadline'])): ?>
                <span><i class="fas fa-clock me-1"></i> Closes <?= htmlspecialchars(date('M j, Y', strtotime($job['deadline']))) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex flex-column gap-1 text-center" style="flex-shrink:0;min-width:120px;padding:0.5rem 1rem;background:rgba(255,255,255,0.12);border-radius:var(--ss-r);">
            <div style="font-size:2rem;font-weight:800;color:#fff;line-height:1;"><?= $appCount ?></div>
            <div style="font-size:0.72rem;color:rgba(255,255,255,0.85);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Total Applicants</div>
        </div>
    </div>
</div>

<!-- ============== STAT CARDS BY STATUS ============== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-clock',        'label' => 'Pending',     'count' => $counts['pending'] ?? 0,     'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-star',         'label' => 'Shortlisted', 'count' => $counts['shortlisted'] ?? 0, 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-video',        'label' => 'Interview',   'count' => $counts['interview'] ?? 0,   'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle', 'label' => 'Offered',     'count' => $counts['offered'] ?? 0,     'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-times-circle', 'label' => 'Rejected',    'count' => $counts['rejected'] ?? 0,    'color' => 'danger']) ?>
</div>

<?php if (empty($applicants)): ?>
    <div class="ss-card ss-animate-fade-up">
        <div class="ss-card-body">
            <?= Component::emptyState([
                'icon'   => 'fa-users',
                'title'  => 'No applicants yet',
                'desc'   => "No one has applied to \"" . htmlspecialchars($jobTitle) . "\" yet. Share the job link on social media or your careers page to attract candidates.",
                'action' => '<a href="' . URL::to('employer/jobs') . '" class="ss-btn ss-btn-soft"><i class="fas fa-arrow-left"></i> Back to Jobs</a>'
            ]) ?>
        </div>
    </div>
<?php else: ?>

<!-- ============== TABS + TABLE ============== -->
<div data-tabs class="ss-animate-fade-up">
    <div class="ss-tabs">
        <button class="ss-tab active" data-tab="#app-all"><i class="fas fa-list"></i> All <span class="count"><?= $counts['all'] ?></span></button>
        <button class="ss-tab" data-tab="#app-pending"><i class="fas fa-clock"></i> Pending <span class="count"><?= $counts['pending'] ?></span></button>
        <button class="ss-tab" data-tab="#app-shortlisted"><i class="fas fa-star"></i> Shortlisted <span class="count"><?= $counts['shortlisted'] ?></span></button>
        <button class="ss-tab" data-tab="#app-interview"><i class="fas fa-video"></i> Interview <span class="count"><?= $counts['interview'] ?></span></button>
        <button class="ss-tab" data-tab="#app-offered"><i class="fas fa-check-circle"></i> Offered <span class="count"><?= $counts['offered'] ?></span></button>
        <button class="ss-tab" data-tab="#app-rejected"><i class="fas fa-times-circle"></i> Rejected <span class="count"><?= $counts['rejected'] ?></span></button>
    </div>

    <!-- ALL applicants -->
    <div class="ss-tab-pane active" id="app-all">
        <div class="ss-table-wrap" data-table>
            <div class="ss-table-toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search by name, university, department..." data-table-search>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export CSV</span></button>
                    <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
                </div>
            </div>
            <div class="table-responsive-2">
                <table class="ss-table">
                    <thead>
                        <tr>
                            <th data-sort="candidate">Candidate <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="university">University <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="department">Department <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="applied">Applied <i class="fas fa-sort sort-icon"></i></th>
                            <th class="no-sort">Update Status</th>
                            <th class="no-sort text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $a):
                            $name = $fullName($a);
                            $ini  = $initials($a);
                            $appliedTs = strtotime($a['applied_at'] ?? 'now');
                        ?>
                        <tr data-app-id="<?= (int)($a['id'] ?? 0) ?>">
                            <td>
                                <div class="table-avatar">
                                    <div class="ss-avatar ss-avatar-md" style="background:var(--ss-grad-primary);color:#fff;"><?= htmlspecialchars($ini) ?></div>
                                    <div style="min-width:0;">
                                        <div class="fw-semibold ss-truncate"><?= htmlspecialchars($name ?: 'Applicant') ?></div>
                                        <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($a['email'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($a['uni_name'] ?? '—') ?></td>
                            <td>
                                <?php if (!empty($a['department'])): ?>
                                    <span class="ss-chip"><?= htmlspecialchars($a['department']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--ss-text-3);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.82rem;color:var(--ss-text-2);">
                                <div><?= htmlspecialchars(date('M j, Y', $appliedTs)) ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">
                                    <?= htmlspecialchars(timeAgo($a['applied_at'] ?? '')) ?>
                                </div>
                            </td>
                            <td>
                                <select class="ss-select app-status-select" data-app-id="<?= (int)($a['id'] ?? 0) ?>" style="width:auto;min-width:150px;font-size:0.8rem;padding:0.4rem 0.7rem;">
                                    <?php foreach ($statusMeta as $key => $m): ?>
                                        <option value="<?= $key ?>" <?= ($a['status'] ?? 'pending') === $key ? 'selected' : '' ?>><?= htmlspecialchars($m['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-end">
                                <button type="button" class="ss-btn ss-btn-ghost ss-btn-sm" data-app-action="shortlist" data-app-id="<?= (int)($a['id'] ?? 0) ?>" title="Shortlist"><i class="fas fa-star"></i></button>
                                <button type="button" class="ss-btn ss-btn-ghost ss-btn-sm" data-app-action="reject" data-app-id="<?= (int)($a['id'] ?? 0) ?>" title="Reject" style="color:var(--ss-danger);"><i class="fas fa-times"></i></button>
                                <a href="mailto:<?= htmlspecialchars($a['email'] ?? '') ?>" class="ss-btn ss-btn-ghost ss-btn-sm" title="Email"><i class="fas fa-envelope"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filtered panes -->
    <?php foreach (['pending' => 'Pending', 'shortlisted' => 'Shortlisted', 'interview' => 'Interview', 'offered' => 'Offered', 'rejected' => 'Rejected'] as $key => $label):
        $filtered = array_filter($applicants, fn($a) => ($a['status'] ?? 'pending') === $key);
    ?>
    <div class="ss-tab-pane" id="app-<?= $key ?>">
        <div class="ss-table-wrap">
            <div class="ss-table-toolbar">
                <div class="fw-semibold" style="font-size:0.9rem;">
                    <i class="fas fa-filter text-primary me-1"></i>
                    Showing applicants with status:
                    <?= Component::badge($label, $statusMeta[$key]['color']) ?>
                </div>
                <div class="ms-auto" style="font-size:0.8rem;color:var(--ss-text-3);">
                    <?= count($filtered) ?> candidate<?= count($filtered) === 1 ? '' : 's' ?>
                </div>
            </div>
            <div class="table-responsive-2">
                <table class="ss-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>University</th>
                            <th>Department</th>
                            <th>Applied</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filtered)): ?>
                            <tr><td colspan="5" style="text-align:center;padding:2.5rem;color:var(--ss-text-3);">
                                <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:0.4;"></i>
                                No applicants with status "<?= htmlspecialchars($label) ?>" yet.
                            </td></tr>
                        <?php else: foreach ($filtered as $a): ?>
                            <tr>
                                <td>
                                    <div class="table-avatar">
                                        <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);color:#fff;"><?= htmlspecialchars($initials($a)) ?></div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($fullName($a)) ?></div>
                                            <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($a['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($a['uni_name'] ?? '—') ?></td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($a['department'] ?? '—') ?></td>
                                <td style="font-size:0.82rem;"><?= htmlspecialchars(date('M j, Y', strtotime($a['applied_at'] ?? 'now'))) ?></td>
                                <td><?= $renderStatusBadge($a['status'] ?? $key) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<!-- ============== STATUS UPDATE SCRIPT ============== -->
<script>
(function() {
    // Parse the CSRF field to extract name/value (we re-use it for fetch bodies)
    const csrfHtml = <?= json_encode($csrfField ?? '') ?>;
    const csrfField = (function() {
        const tmp = document.createElement('div');
        tmp.innerHTML = csrfHtml;
        const inp = tmp.querySelector('input');
        return inp ? { name: inp.name, value: inp.value } : null;
    })();

    function updateStatus(appId, status) {
        if (!csrfField) {
            window.SS && window.SS.Toast && window.SS.Toast.show('Missing CSRF token — refresh the page', 'error');
            return;
        }
        const fd = new FormData();
        fd.append(csrfField.name, csrfField.value);
        fd.append('status', status);
        fd.append('_token', csrfField.value);

        fetch('<?= URL::to('employer/applications') ?>/' + encodeURIComponent(appId) + '/status', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            const toast = (window.SS && window.SS.Toast) ? window.SS.Toast : window.ssToast;
            if (data.success) {
                toast && toast.show('Status updated to ' + status, 'success');
            } else {
                toast && toast.show(data.message || 'Failed to update status', 'error');
            }
        })
        .catch(function() {
            const toast = (window.SS && window.SS.Toast) ? window.SS.Toast : window.ssToast;
            toast && toast.show('Network error — please try again', 'error');
        });
    }

    document.querySelectorAll('.app-status-select').forEach(function(sel) {
        sel.addEventListener('change', function(e) {
            updateStatus(e.target.dataset.appId, e.target.value);
        });
    });

    document.querySelectorAll('[data-app-action]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const appId = btn.dataset.appId;
            const action = btn.dataset.appAction;
            const map = { shortlist: 'shortlisted', reject: 'rejected' };
            if (!map[action]) return;
            const sel = document.querySelector('.app-status-select[data-app-id="' + appId + '"]');
            if (sel) { sel.value = map[action]; sel.dispatchEvent(new Event('change')); }
        });
    });
})();
</script>
