<?php
/**
 * Admin — Manage Internships (Premium redesigned, Stripe-quality)
 *
 * Data passed from AdminController::manageInternships():
 *   $internships — paginated array: data[], total, current_page, per_page, last_page
 *
 * Each row in data[]: title, company_name, employer_name, duration, duration_unit,
 *                     allowance, location, deadline, positions_available, status, created_at
 *
 * AJAX form: POST /admin/internships/{id}/status with field name="status" (any string)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Manage Internships';

$internships = $internships ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1];
$list        = $internships['data'] ?? [];
$total       = (int)($internships['total'] ?? 0);
$currentPage = (int)($internships['current_page'] ?? 1);
$lastPage    = (int)($internships['last_page'] ?? 1);
$perPage     = (int)($internships['per_page'] ?? 15);

$statusColors = [
    'published' => 'success',
    'draft'     => 'soft',
    'closed'    => 'danger',
    'archived'  => 'secondary',
];

// Sample-based stats
$activeCount = 0; $draftCount = 0; $positionsOpen = 0;
foreach ($list as $i) {
    $st = $i['status'] ?? '';
    if ($st === 'published') $activeCount++;
    elseif ($st === 'draft') $draftCount++;
    $positionsOpen += (int)($i['positions_available'] ?? 0);
}

$fmtMoney = function($n) {
    $n = (int)$n;
    if ($n >= 1000) return '$' . number_format($n / 1000, 1) . 'k';
    return '$' . number_format($n);
};
?>
<?= Component::pageHeader(
    'Manage Internships 🎓',
    '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Internships</span>',
    '<button class="ss-btn ss-btn-light" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export</span></button>' .
    '<a href="' . URL::to('admin/jobs') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-briefcase"></i> <span class="d-none d-md-inline">View Jobs</span></a>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-graduation-cap', 'label' => 'Total Internships', 'count' => $total,         'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',   'label' => 'Published',         'count' => $activeCount,    'color' => 'success', 'trend' => 'On this page']) ?>
    <?= Component::statCard(['icon' => 'fa-pen',             'label' => 'Draft',             'count' => $draftCount,     'color' => 'warning', 'trend' => 'Unpublished']) ?>
    <?= Component::statCard(['icon' => 'fa-users',          'label' => 'Open Positions',    'count' => $positionsOpen,  'color' => 'info',    'trend' => 'Available slots', 'trendUp' => true]) ?>
</div>

<!-- ==================== INTERNSHIPS TABLE ==================== -->
<div class="ss-table-wrap ss-animate-fade-up" data-table>
    <!-- Toolbar -->
    <div class="ss-table-toolbar">
        <form method="GET" action="<?= URL::to('admin/internships') ?>" class="d-flex gap-2 flex-wrap" style="flex:1;min-width:240px;">
            <?= $csrfField ?? '' ?>
            <div class="search-box" style="max-width:340px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search internships by title or company..." data-table-search>
            </div>
            <select name="status" class="ss-select" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (['published', 'draft', 'closed', 'archived'] as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>"><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <div class="ms-auto d-flex gap-2">
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">CSV</span></button>
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
        </div>
    </div>

    <?php if (empty($list)): ?>
        <div style="padding:2rem;">
            <?= Component::emptyState([
                'icon'   => 'fa-graduation-cap',
                'title'  => 'No internships found',
                'desc'   => 'No internships match your filters yet. Once employers post internships, they will appear here for moderation.',
                'action' => '<a href="' . URL::to('admin/internships') . '" class="ss-btn ss-btn-soft">Clear filters</a>'
            ]) ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Internship Title</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Duration</th>
                    <th>Allowance</th>
                    <th>Positions</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $i):
                    $status    = $i['status'] ?? 'draft';
                    $statColor = $statusColors[$status] ?? 'soft';
                    $duration  = trim(($i['duration'] ?? '') . ' ' . ($i['duration_unit'] ?? ''));
                    $allowance = !empty($i['allowance']) ? $fmtMoney($i['allowance']) . '/mo' : 'Unpaid';
                    $positions = (int)($i['positions_available'] ?? 0);
                    $deadlineTs = strtotime($i['deadline'] ?? '');
                    $deadlinePassed = $deadlineTs && $deadlineTs < time();
                ?>
                <tr>
                    <td>
                        <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($i['title'] ?? 'Untitled') ?></div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">Posted <?= htmlspecialchars(date('M j, Y', strtotime($i['created_at'] ?? 'now'))) ?></div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-grad-primary);"><?= strtoupper(substr($i['company_name'] ?? $i['employer_name'] ?? 'C', 0, 1)) ?></div>
                            <div>
                                <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($i['company_name'] ?? '—') ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars($i['employer_name'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.8rem;">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        <?= htmlspecialchars($i['location'] ?? 'Remote') ?>
                    </td>
                    <td style="font-size:0.8rem;color:var(--ss-text-2);">
                        <?php if ($duration): ?>
                            <i class="fas fa-clock text-info"></i> <?= htmlspecialchars($duration) ?>
                        <?php else: ?>
                            <span style="color:var(--ss-text-3);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;font-weight:600;color:var(--ss-text);">
                        <?= htmlspecialchars($allowance) ?>
                    </td>
                    <td>
                        <span class="ss-badge ss-badge-<?= $positions > 5 ? 'success' : ($positions > 0 ? 'info' : 'soft') ?>">
                            <i class="fas fa-users"></i> <?= $positions ?> open
                        </span>
                    </td>
                    <td style="font-size:0.78rem;color:<?= $deadlinePassed ? 'var(--ss-danger)' : 'var(--ss-text-2)' ?>;">
                        <?php if ($deadlineTs): ?>
                            <div><?= htmlspecialchars(date('M j, Y', $deadlineTs)) ?></div>
                            <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= $deadlinePassed ? 'Passed' : 'Open' ?></div>
                        <?php else: ?>
                            <span style="color:var(--ss-text-3);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="<?= URL::to('admin/internships/' . urlencode($i['id'] ?? '') . '/status') ?>" class="internship-status-form" data-internship-status>
                            <?= $csrfField ?? '' ?>
                            <select class="ss-select internship-status-select"
                                    name="status"
                                    data-internship-id="<?= htmlspecialchars($i['id'] ?? '') ?>"
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
                                <a class="dropdown-item" href="<?= URL::to('student/internships/' . urlencode($i['id'] ?? '')) ?>"><i class="fas fa-eye"></i> View listing</a>
                                <a class="dropdown-item" href="#" onclick='openEditInternshipModal(<?= json_encode([
                                    "id" => (int)$i["id"],
                                    "title" => $i["title"] ?? "",
                                    "description" => $i["description"] ?? "",
                                    "location" => $i["location"] ?? "",
                                    "duration" => (int)($i["duration"] ?? 3),
                                    "duration_unit" => $i["duration_unit"] ?? "months",
                                    "allowance" => $i["allowance"] ?? "",
                                    "positions_available" => (int)($i["positions_available"] ?? 1),
                                    "deadline" => $i["deadline"] ?? "",
                                    "status" => $i["status"] ?? "published",
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>);return false;'><i class="fas fa-pen"></i> Edit</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="confirmDeleteInternship(<?= (int)($i['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($i['title'] ?? '')) ?>');return false;"><i class="fas fa-trash"></i> Remove</a>
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
        <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= $total ?> internships</div>
        <div class="ss-pagination">
            <?php
            $baseQ = '?page=';
            if ($currentPage > 1): ?>
                <a class="page-btn" href="<?= URL::to('admin/internships' . $baseQ . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif;
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1) {
                echo '<a class="page-btn" href="' . URL::to('admin/internships' . $baseQ . '1') . '">1</a>';
                if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
            }
            for ($p = $start; $p <= $end; $p++):
            ?>
                <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('admin/internships' . $baseQ . $p) ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $lastPage) {
                if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                echo '<a class="page-btn" href="' . URL::to('admin/internships' . $baseQ . $lastPage) . '">' . $lastPage . '</a>';
            }
            ?>
            <?php if ($currentPage < $lastPage): ?>
                <a class="page-btn" href="<?= URL::to('admin/internships' . $baseQ . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
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
    document.querySelectorAll('.internship-status-select').forEach(function(sel) {
        sel.addEventListener('change', async function() {
            const form = this.closest('form[data-internship-status]');
            if (!form) return;
            const fd = new FormData(form);
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                if (window.ssToast) ssToast.show(json.message || 'Internship status updated', json.success ? 'success' : 'error');
            } catch (e) {
                if (window.ssToast) ssToast.show('Failed to update internship status', 'error');
            }
        });
    });
})();

function openEditInternshipModal(i) {
    document.getElementById('editInternshipForm').action = '<?= URL::to('admin/internships') ?>/' + i.id + '/update';
    document.getElementById('edit_internship_title').value = i.title || '';
    document.getElementById('edit_internship_description').value = i.description || '';
    document.getElementById('edit_internship_location').value = i.location || '';
    document.getElementById('edit_internship_duration').value = i.duration || 3;
    document.getElementById('edit_internship_duration_unit').value = i.duration_unit || 'months';
    document.getElementById('edit_internship_allowance').value = i.allowance || '';
    document.getElementById('edit_internship_positions').value = i.positions_available || 1;
    document.getElementById('edit_internship_deadline').value = i.deadline ? i.deadline.substring(0, 10) : '';
    document.getElementById('edit_internship_status').value = i.status || 'published';
    new bootstrap.Modal(document.getElementById('editInternshipModal')).show();
}

let deleteInternshipId = 0;
function confirmDeleteInternship(id, title) {
    deleteInternshipId = id;
    document.getElementById('deleteInternshipTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteInternshipModal')).show();
}
async function executeInternshipDelete() {
    const btn = document.getElementById('deleteInternshipConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    try {
        const fd = new FormData();
        fd.append('_token', document.querySelector('input[name="_token"]')?.value || '');
        const res = await fetch('<?= URL::to('admin/internships/') ?>' + deleteInternshipId + '/delete', {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('deleteInternshipModal'))?.hide();
            if (window.ssToast) ssToast.show(data.message || 'Internship deleted', 'success');
            setTimeout(() => window.location.reload(), 700);
        } else {
            if (window.ssToast) ssToast.show(data.message || 'Failed to delete internship', 'error');
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

<!-- ==================== EDIT INTERNSHIP MODAL ==================== -->
<div class="modal fade" id="editInternshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editInternshipForm" action="" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary"></i> Edit Internship</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="edit_internship_title" placeholder=" " required minlength="3">
                        <label for="edit_internship_title">Internship Title *</label>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_internship_description">Description *</label>
                        <textarea name="description" id="edit_internship_description" class="ss-textarea" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="location" id="edit_internship_location" placeholder=" " required>
                                <label for="edit_internship_location">Location *</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="duration" id="edit_internship_duration" min="1" placeholder=" " required>
                                <label for="edit_internship_duration">Duration *</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="ss-form-group ss-float">
                                <select name="duration_unit" id="edit_internship_duration_unit" required>
                                    <option value="weeks">Weeks</option>
                                    <option value="months">Months</option>
                                </select>
                                <label for="edit_internship_duration_unit">Unit</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="allowance" id="edit_internship_allowance" min="0" step="1000" placeholder=" ">
                                <label for="edit_internship_allowance">Allowance (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="positions" id="edit_internship_positions" min="1" placeholder=" " required>
                                <label for="edit_internship_positions">Positions *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="date" name="deadline" id="edit_internship_deadline" placeholder=" ">
                                <label for="edit_internship_deadline">Deadline</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="status" id="edit_internship_status" required>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="closed">Closed</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <label for="edit_internship_status">Status</label>
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

<!-- ==================== DELETE INTERNSHIP MODAL ==================== -->
<div class="modal fade" id="deleteInternshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid var(--ss-border);">
                <h5 class="modal-title" style="color:var(--ss-danger);"><i class="fas fa-exclamation-triangle me-2"></i> Remove Internship</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently remove <strong id="deleteInternshipTitle"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ss-border);">
                <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ss-btn ss-btn-danger" id="deleteInternshipConfirmBtn" onclick="executeInternshipDelete()"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>
</div>
