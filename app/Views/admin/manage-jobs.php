<?php
/**
 * Admin — Manage Jobs (Premium redesigned, Stripe-quality)
 *
 * Data passed from AdminController::manageJobs():
 *   $jobs — paginated array: data[], total, current_page, per_page, last_page
 *
 * Each row in data[]: title, company_name, employer_name, location, type,
 *                     salary_min, salary_max, deadline, status, views_count, created_at
 *
 * AJAX form: POST /admin/jobs/{id}/status with field name="status" (any string)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Manage Jobs';

$jobs = $jobs ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1];
$jobList      = $jobs['data'] ?? [];
$total        = (int)($jobs['total'] ?? 0);
$currentPage  = (int)($jobs['current_page'] ?? 1);
$lastPage     = (int)($jobs['last_page'] ?? 1);
$perPage      = (int)($jobs['per_page'] ?? 15);

$statusColors = [
    'published' => 'success',
    'draft'     => 'soft',
    'closed'    => 'danger',
    'archived'  => 'secondary',
];
$typeColors = [
    'full-time' => 'primary',
    'part-time' => 'info',
    'contract'  => 'warning',
    'freelance' => 'success',
];

// Sample-based stats
$activeJobs = 0; $draftJobs = 0; $closedJobs = 0; $totalViews = 0;
foreach ($jobList as $j) {
    $st = $j['status'] ?? '';
    if ($st === 'published') $activeJobs++;
    elseif ($st === 'draft') $draftJobs++;
    elseif (in_array($st, ['closed', 'archived'], true)) $closedJobs++;
    $totalViews += (int)($j['views_count'] ?? 0);
}

$fmtMoney = function($n) {
    $n = (int)$n;
    if ($n >= 1000) return '$' . number_format($n / 1000, 1) . 'k';
    return '$' . $n;
};
?>
<?= Component::pageHeader(
    'Manage Jobs 💼',
    '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Jobs</span>',
    '<button class="ss-btn ss-btn-light" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export</span></button>' .
    '<a href="' . URL::to('admin/dashboard') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-chart-line"></i> <span class="d-none d-md-inline">Analytics</span></a>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-briefcase',     'label' => 'Total Jobs',   'count' => $total,       'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',  'label' => 'Published',    'count' => $activeJobs,  'color' => 'success', 'trend' => 'On this page']) ?>
    <?= Component::statCard(['icon' => 'fa-pen',            'label' => 'Draft',        'count' => $draftJobs,   'color' => 'warning', 'trend' => 'Unpublished']) ?>
    <?= Component::statCard(['icon' => 'fa-eye',           'label' => 'Total Views',  'count' => $totalViews,  'color' => 'info',    'trend' => 'This page', 'trendUp' => true]) ?>
</div>

<!-- ==================== JOBS TABLE ==================== -->
<div class="ss-table-wrap ss-animate-fade-up" data-table>
    <!-- Toolbar -->
    <div class="ss-table-toolbar">
        <form method="GET" action="<?= URL::to('admin/jobs') ?>" class="d-flex gap-2 flex-wrap" style="flex:1;min-width:240px;">
            <?= $csrfField ?? '' ?>
            <div class="search-box" style="max-width:340px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search jobs by title or company..." data-table-search>
            </div>
            <select name="status" class="ss-select" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (['published', 'draft', 'closed', 'archived'] as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>"><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" class="ss-select" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach (['full-time', 'part-time', 'contract', 'freelance'] as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>"><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <div class="ms-auto d-flex gap-2">
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">CSV</span></button>
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
        </div>
    </div>

    <?php if (empty($jobList)): ?>
        <div style="padding:2rem;">
            <?= Component::emptyState([
                'icon'   => 'fa-briefcase',
                'title'  => 'No jobs found',
                'desc'   => 'No jobs match your filters yet. Once employers post jobs, they will appear here for moderation.',
                'action' => '<a href="' . URL::to('admin/jobs') . '" class="ss-btn ss-btn-soft">Clear filters</a>'
            ]) ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Salary</th>
                    <th>Deadline</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobList as $j):
                    $status    = $j['status'] ?? 'draft';
                    $statColor = $statusColors[$status] ?? 'soft';
                    $type      = $j['type'] ?? 'full-time';
                    $typeColor = $typeColors[$type] ?? 'soft';
                    $salary    = '';
                    if (!empty($j['salary_min']) || !empty($j['salary_max'])) {
                        $salary = $fmtMoney($j['salary_min'] ?? 0) . ' – ' . $fmtMoney($j['salary_max'] ?? 0);
                    } else {
                        $salary = '—';
                    }
                    $deadlineTs = strtotime($j['deadline'] ?? '');
                    $deadlinePassed = $deadlineTs && $deadlineTs < time();
                ?>
                <tr>
                    <td>
                        <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($j['title'] ?? 'Untitled') ?></div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">Posted <?= htmlspecialchars(date('M j, Y', strtotime($j['created_at'] ?? 'now'))) ?></div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-cool);"><?= strtoupper(substr($j['company_name'] ?? $j['employer_name'] ?? 'C', 0, 1)) ?></div>
                            <div>
                                <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($j['company_name'] ?? '—') ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars($j['employer_name'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.8rem;">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        <?= htmlspecialchars($j['location'] ?? 'Remote') ?>
                    </td>
                    <td>
                        <span class="ss-badge ss-badge-<?= $typeColor ?> text-capitalize"><?= htmlspecialchars($type) ?></span>
                    </td>
                    <td style="font-size:0.78rem;font-weight:600;color:var(--ss-text);"><?= htmlspecialchars($salary) ?></td>
                    <td style="font-size:0.78rem;color:<?= $deadlinePassed ? 'var(--ss-danger)' : 'var(--ss-text-2)' ?>;">
                        <?php if ($deadlineTs): ?>
                            <div><?= htmlspecialchars(date('M j, Y', $deadlineTs)) ?></div>
                            <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= $deadlinePassed ? 'Passed' : 'Open' ?></div>
                        <?php else: ?>
                            <span style="color:var(--ss-text-3);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;font-weight:600;color:var(--ss-text-2);">
                            <i class="fas fa-eye"></i> <?= number_format((int)($j['views_count'] ?? 0)) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="<?= URL::to('admin/jobs/' . urlencode($j['id'] ?? '') . '/status') ?>" class="job-status-form" data-job-status>
                            <?= $csrfField ?? '' ?>
                            <select class="ss-select job-status-select"
                                    name="status"
                                    data-job-id="<?= htmlspecialchars($j['id'] ?? '') ?>"
                                    style="width:auto;min-width:130px;font-size:0.78rem;font-weight:600;">
                                <?php foreach (['published', 'draft', 'closed', 'archived'] as $st):
                                    $sc = $statusColors[$st] ?? 'soft';
                                ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="ss-btn ss-btn-ghost ss-btn-sm" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="<?= URL::to('student/jobs/' . urlencode($j['id'] ?? '')) ?>"><i class="fas fa-eye"></i> View listing</a>
                                <a class="dropdown-item" href="#" onclick='openEditJobModal(<?= json_encode([
                                    "id" => (int)$j["id"],
                                    "title" => $j["title"] ?? "",
                                    "description" => $j["description"] ?? "",
                                    "location" => $j["location"] ?? "",
                                    "type" => $j["type"] ?? "full-time",
                                    "salary_min" => $j["salary_min"] ?? "",
                                    "salary_max" => $j["salary_max"] ?? "",
                                    "deadline" => $j["deadline"] ?? "",
                                    "status" => $j["status"] ?? "published",
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>);return false;'><i class="fas fa-pen"></i> Edit job</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="confirmDeleteJob(<?= (int)($j['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($j['title'] ?? '')) ?>');return false;"><i class="fas fa-trash"></i> Remove</a>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($lastPage > 1): ?>
    <div class="ss-table-pagination">
        <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= $total ?> jobs</div>
        <div class="ss-pagination">
            <?php
            $baseQ = '?page=';
            if ($currentPage > 1): ?>
                <a class="page-btn" href="<?= URL::to('admin/jobs' . $baseQ . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif;
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1) {
                echo '<a class="page-btn" href="' . URL::to('admin/jobs' . $baseQ . '1') . '">1</a>';
                if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
            }
            for ($p = $start; $p <= $end; $p++):
            ?>
                <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('admin/jobs' . $baseQ . $p) ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $lastPage) {
                if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                echo '<a class="page-btn" href="' . URL::to('admin/jobs' . $baseQ . $lastPage) . '">' . $lastPage . '</a>';
            }
            ?>
            <?php if ($currentPage < $lastPage): ?>
                <a class="page-btn" href="<?= URL::to('admin/jobs' . $baseQ . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function() {
    document.querySelectorAll('.job-status-select').forEach(function(sel) {
        sel.addEventListener('change', async function() {
            const form = this.closest('form[data-job-status]');
            if (!form) return;
            const fd = new FormData(form);
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                if (typeof window.showToast === 'function') {
                    window.showToast(json.message || 'Job status updated', json.success ? 'success' : 'danger');
                } else if (window.ssToast) {
                    ssToast.show(json.message || 'Job status updated', json.success ? 'success' : 'error');
                }
            } catch (e) {
                if (window.ssToast) ssToast.show('Failed to update job status', 'error');
            }
        });
    });
})();

function openEditJobModal(job) {
    document.getElementById('editJobForm').action = '<?= URL::to('admin/jobs') ?>/' + job.id + '/update';
    document.getElementById('edit_job_title').value = job.title || '';
    document.getElementById('edit_job_description').value = job.description || '';
    document.getElementById('edit_job_location').value = job.location || '';
    document.getElementById('edit_job_type').value = job.type || 'full-time';
    document.getElementById('edit_job_salary_min').value = job.salary_min || '';
    document.getElementById('edit_job_salary_max').value = job.salary_max || '';
    document.getElementById('edit_job_deadline').value = job.deadline ? job.deadline.substring(0, 10) : '';
    document.getElementById('edit_job_status').value = job.status || 'published';
    new bootstrap.Modal(document.getElementById('editJobModal')).show();
}

let deleteJobId = 0;
function confirmDeleteJob(id, title) {
    deleteJobId = id;
    document.getElementById('deleteJobTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteJobModal')).show();
}
async function executeJobDelete() {
    const btn = document.getElementById('deleteJobConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    try {
        const fd = new FormData();
        fd.append('_token', document.querySelector('input[name="_token"]')?.value || '');
        const res = await fetch('<?= URL::to('admin/jobs/') ?>' + deleteJobId + '/delete', {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('deleteJobModal'))?.hide();
            if (window.ssToast) ssToast.show(data.message || 'Job deleted', 'success');
            setTimeout(() => window.location.reload(), 700);
        } else {
            if (window.ssToast) ssToast.show(data.message || 'Failed to delete job', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
    } catch (e) {
        if (window.ssToast) ssToast.show('Network error', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
}
</script>

<!-- ==================== EDIT JOB MODAL ==================== -->
<div class="modal fade" id="editJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editJobForm" action="" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary"></i> Edit Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="edit_job_title" placeholder=" " required minlength="3">
                        <label for="edit_job_title">Job Title *</label>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_job_description">Description *</label>
                        <textarea name="description" id="edit_job_description" class="ss-textarea" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="location" id="edit_job_location" placeholder=" " required>
                                <label for="edit_job_location">Location *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="type" id="edit_job_type" required>
                                    <option value="full-time">Full-time</option>
                                    <option value="part-time">Part-time</option>
                                    <option value="contract">Contract</option>
                                    <option value="freelance">Freelance</option>
                                </select>
                                <label for="edit_job_type">Type *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="salary_min" id="edit_job_salary_min" min="0" placeholder=" ">
                                <label for="edit_job_salary_min">Min Salary</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="salary_max" id="edit_job_salary_max" min="0" placeholder=" ">
                                <label for="edit_job_salary_max">Max Salary</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="date" name="deadline" id="edit_job_deadline" placeholder=" ">
                                <label for="edit_job_deadline">Deadline</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="status" id="edit_job_status" required>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="closed">Closed</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <label for="edit_job_status">Status</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== DELETE JOB MODAL ==================== -->
<div class="modal fade" id="deleteJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid var(--ss-border);">
                <h5 class="modal-title" style="color:var(--ss-danger);"><i class="fas fa-exclamation-triangle me-2"></i> Remove Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently remove <strong id="deleteJobTitle"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ss-border);">
                <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ss-btn ss-btn-danger" id="deleteJobConfirmBtn" onclick="executeJobDelete()"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>
</div>
