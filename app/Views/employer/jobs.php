<?php
/**
 * Employer — My Jobs (premium redesign v3)
 *
 * Data passed from EmployerController::jobs():
 *   $jobs — paginated array with keys: data[], total, current_page, per_page, last_page
 *
 * Each row in data[] has: id, title, company_name, location, type, salary_min, salary_max,
 *                         deadline, status, views_count, applicant_count
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'My Jobs';

$jobs        = $jobs ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 10, 'last_page' => 1];
$jobList     = $jobs['data'] ?? [];
$total       = (int)($jobs['total'] ?? 0);
$currentPage = (int)($jobs['current_page'] ?? 1);
$lastPage    = (int)($jobs['last_page'] ?? 1);

// Derived totals for stat cards
$totalViews  = 0; $totalApps = 0; $activeJobs = 0;
foreach ($jobList as $j) {
    $totalViews += (int)($j['views_count'] ?? 0);
    $totalApps  += (int)($j['applicant_count'] ?? 0);
    if (($j['status'] ?? '') === 'published') $activeJobs++;
}

$statusMeta = [
    'published' => ['color' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Active'],
    'draft'     => ['color' => 'soft',    'icon' => 'fa-pen',          'label' => 'Draft'],
    'paused'    => ['color' => 'warning', 'icon' => 'fa-pause',        'label' => 'Paused'],
    'closed'    => ['color' => 'danger',  'icon' => 'fa-lock',         'label' => 'Closed'],
];

$renderStatusBadge = function(string $status) use ($statusMeta): string {
    $m = $statusMeta[$status] ?? ['color' => 'soft', 'icon' => 'fa-circle', 'label' => ucfirst($status)];
    return '<span class="ss-badge ss-badge-' . $m['color'] . '"><i class="fas ' . $m['icon'] . '"></i> ' . htmlspecialchars($m['label']) . '</span>';
};
?>
<?= Component::pageHeader(
    'My Jobs',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <span>My Jobs</span>',
    '<a href="' . URL::to('employer/post-job') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-plus"></i> <span class="d-none d-md-inline">Post a Job</span></a>'
) ?>

<!-- ============== STAT CARDS ============== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-briefcase',     'label' => 'Total Jobs',     'count' => $total,       'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',  'label' => 'Active Jobs',    'count' => $activeJobs, 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-eye',           'label' => 'Total Views',    'count' => $totalViews,  'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-users',         'label' => 'Total Applicants','count' => $totalApps,  'color' => 'warning', 'trend' => '+' . $totalApps, 'trendUp' => true]) ?>
</div>

<!-- ============== TABLE ============== -->
<?php if (empty($jobList)): ?>
    <div class="ss-card ss-animate-fade-up">
        <div class="ss-card-body">
            <?= Component::emptyState([
                'icon'   => 'fa-briefcase',
                'title'  => 'No jobs posted yet',
                'desc'   => "You haven't posted any jobs yet. Post your first job to start receiving applications from talented candidates.",
                'action' => '<a href="' . URL::to('employer/post-job') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-plus"></i> Post your first job</a>'
            ]) ?>
        </div>
    </div>
<?php else: ?>
<div class="ss-table-wrap ss-animate-fade-up" data-table>
    <div class="ss-table-toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search by title, location, type..." data-table-search>
        </div>
        <div class="ms-auto d-flex gap-2">
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export CSV</span></button>
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
            <a href="<?= URL::to('employer/post-job') ?>" class="ss-btn ss-btn-gradient ss-btn-sm"><i class="fas fa-plus"></i> <span class="d-none d-md-inline">New Job</span></a>
        </div>
    </div>
    <div class="table-responsive-2">
        <table class="ss-table">
            <thead>
                <tr>
                    <th data-sort="title">Job Title <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="location">Location <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="salary">Salary <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="deadline">Deadline <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="views">Views <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="applicants">Applicants <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                    <th class="no-sort text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobList as $j):
                    $salary = '';
                    if (!empty($j['salary_min'])) {
                        $salary = number_format($j['salary_min']);
                        if (!empty($j['salary_max'])) $salary .= ' – ' . number_format($j['salary_max']);
                        $salary .= ' RWF';
                    } else {
                        $salary = '<span style="color:var(--ss-text-3);">—</span>';
                    }
                    $deadlineStr = !empty($j['deadline']) ? date('M j, Y', strtotime($j['deadline'])) : '<span style="color:var(--ss-text-3);">—</span>';
                    $isClosed = !empty($j['deadline']) && strtotime($j['deadline']) < time();
                ?>
                <tr>
                    <td>
                        <div class="table-avatar">
                            <div class="avatar" style="background:var(--ss-grad-cool);color:#fff;"><?= strtoupper(substr($j['title'] ?? 'J', 0, 1)) ?></div>
                            <div style="min-width:0;">
                                <div class="fw-semibold ss-truncate"><?= htmlspecialchars($j['title'] ?? 'Untitled') ?></div>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($j['company_name'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.85rem;">
                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                        <?= htmlspecialchars($j['location'] ?? 'Remote') ?>
                    </td>
                    <td><span class="ss-badge ss-badge-info text-capitalize"><?= htmlspecialchars(ucfirst($j['type'] ?? 'full-time')) ?></span></td>
                    <td style="font-size:0.82rem;white-space:nowrap;"><?= $salary ?></td>
                    <td style="font-size:0.82rem;<?= $isClosed ? 'color:var(--ss-danger);' : '' ?>"><?= $deadlineStr ?></td>
                    <td style="font-size:0.85rem;">
                        <i class="fas fa-eye text-info me-1"></i>
                        <?= number_format((int)($j['views_count'] ?? 0)) ?>
                    </td>
                    <td>
                        <a href="<?= URL::to('employer/jobs/' . (int)$j['id'] . '/applicants') ?>" class="fw-bold" style="color:var(--ss-primary);text-decoration:none;">
                            <i class="fas fa-users me-1"></i><?= (int)($j['applicant_count'] ?? 0) ?>
                        </a>
                    </td>
                    <td><?= $renderStatusBadge($j['status'] ?? 'published') ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="<?= URL::to('employer/jobs/' . (int)$j['id'] . '/applicants') ?>" class="ss-btn ss-btn-soft ss-btn-sm" title="View Applicants"><i class="fas fa-users"></i></a>
                            <button type="button" class="ss-btn ss-btn-light ss-btn-sm" title="Edit Job"
                                onclick='openEditJobModal(<?= json_encode([
                                    "id" => (int)$j["id"],
                                    "title" => $j["title"] ?? "",
                                    "description" => $j["description"] ?? "",
                                    "requirements" => $j["requirements"] ?? "",
                                    "responsibilities" => $j["responsibilities"] ?? "",
                                    "salary_min" => $j["salary_min"] ?? "",
                                    "salary_max" => $j["salary_max"] ?? "",
                                    "location" => $j["location"] ?? "",
                                    "type" => $j["type"] ?? "full-time",
                                    "remote" => (int)($j["remote"] ?? 0),
                                    "deadline" => $j["deadline"] ?? "",
                                    "status" => $j["status"] ?? "published",
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="<?= URL::to('employer/jobs/' . (int)$j['id'] . '/delete') ?>" onsubmit="return confirm('Delete this job? This cannot be undone.');" style="display:inline;">
                                <?= $csrfField ?? '' ?>
                                <button type="submit" class="ss-btn ss-btn-ghost ss-btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
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
        <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= number_format($total) ?> jobs</div>
        <div class="ss-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="page-btn" href="<?= URL::to('employer/jobs?page=' . ($currentPage - 1)) ?>" title="Previous"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif; ?>
            <?php
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1) {
                echo '<a class="page-btn" href="' . URL::to('employer/jobs?page=1') . '">1</a>';
                if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
            }
            for ($p = $start; $p <= $end; $p++):
            ?>
                <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('employer/jobs?page=' . $p) ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $lastPage) {
                if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                echo '<a class="page-btn" href="' . URL::to('employer/jobs?page=' . $lastPage) . '">' . $lastPage . '</a>';
            }
            ?>
            <?php if ($currentPage < $lastPage): ?>
                <a class="page-btn" href="<?= URL::to('employer/jobs?page=' . ($currentPage + 1)) ?>" title="Next"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============== EDIT JOB MODAL ============== -->
<div class="modal fade" id="editJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editJobForm" action="" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary me-2"></i> Edit Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="edit_job_title" placeholder=" " required minlength="3">
                        <label for="edit_job_title">Job Title <span class="req">*</span></label>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_job_description">Description <span class="req">*</span></label>
                        <textarea name="description" id="edit_job_description" class="ss-textarea" required minlength="20"></textarea>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_job_requirements">Requirements</label>
                        <textarea name="requirements" id="edit_job_requirements" class="ss-textarea"></textarea>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_job_responsibilities">Responsibilities</label>
                        <textarea name="responsibilities" id="edit_job_responsibilities" class="ss-textarea"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="salary_min" id="edit_job_salary_min" min="0" placeholder=" ">
                                <label for="edit_job_salary_min">Min Salary (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="salary_max" id="edit_job_salary_max" min="0" placeholder=" ">
                                <label for="edit_job_salary_max">Max Salary (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="location" id="edit_job_location" placeholder=" " required>
                                <label for="edit_job_location">Location <span class="req">*</span></label>
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
                                <label for="edit_job_type">Type <span class="req">*</span></label>
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
                                    <option value="published">Active / Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="paused">Paused</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <label for="edit_job_status">Status</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="d-flex align-items-center gap-2" style="font-size:0.85rem;">
                                <input type="checkbox" name="remote" id="edit_job_remote" value="1"> Remote position
                            </label>
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
<script>
function openEditJobModal(job) {
    document.getElementById('editJobForm').action = '<?= URL::to('employer/jobs') ?>/' + job.id + '/update';
    document.getElementById('edit_job_title').value = job.title || '';
    document.getElementById('edit_job_description').value = job.description || '';
    document.getElementById('edit_job_requirements').value = job.requirements || '';
    document.getElementById('edit_job_responsibilities').value = job.responsibilities || '';
    document.getElementById('edit_job_salary_min').value = job.salary_min || '';
    document.getElementById('edit_job_salary_max').value = job.salary_max || '';
    document.getElementById('edit_job_location').value = job.location || '';
    document.getElementById('edit_job_type').value = job.type || 'full-time';
    document.getElementById('edit_job_deadline').value = job.deadline ? job.deadline.substring(0, 10) : '';
    document.getElementById('edit_job_status').value = job.status || 'published';
    document.getElementById('edit_job_remote').checked = !!job.remote;
    new bootstrap.Modal(document.getElementById('editJobModal')).show();
}
</script>
