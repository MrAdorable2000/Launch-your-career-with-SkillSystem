<?php
/**
 * Employer — Internships (premium redesign v3)
 *
 * Data passed from EmployerController::internships():
 *   $internships — array of internship rows, each with:
 *     title, description, duration, duration_unit, allowance, location, deadline,
 *     positions_available, status, applicant_count
 *
 * Create form submits via POST /employer/internships/store
 * Controller reads: title, description, requirements, duration (getInt), duration_unit,
 *                   allowance (getInt), location, deadline, positions (getInt)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Internships';

$internships = $internships ?? [];

// Derived stats
$totalInter  = count($internships);
$activeInter = count(array_filter($internships, fn($i) => ($i['status'] ?? '') === 'published'));
$totalSpots  = 0; $totalApps = 0;
foreach ($internships as $i) {
    $totalSpots += (int)($i['positions_available'] ?? 0);
    $totalApps  += (int)($i['applicant_count'] ?? 0);
}

$durationUnits = ['weeks' => 'Weeks', 'months' => 'Months', 'years' => 'Years'];

$statusMeta = [
    'published' => ['color' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Active'],
    'draft'     => ['color' => 'soft',    'icon' => 'fa-pen',          'label' => 'Draft'],
    'closed'    => ['color' => 'danger',  'icon' => 'fa-lock',         'label' => 'Closed'],
];
?>
<?= Component::pageHeader(
    'Internships',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <span>Internships</span>',
    '<button type="button" class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#internshipModal"><i class="fas fa-plus"></i> <span class="d-none d-md-inline">Create Internship</span></button>'
) ?>

<!-- ============== STAT CARDS ============== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-user-graduate', 'label' => 'Total Internships', 'count' => $totalInter,  'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',  'label' => 'Active',            'count' => $activeInter, 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-bullseye',      'label' => 'Open Positions',    'count' => $totalSpots,  'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-users',         'label' => 'Total Applicants',  'count' => $totalApps,   'color' => 'warning']) ?>
</div>

<!-- ============== INTERNSHIPS CARD GRID ============== -->
<?php if (empty($internships)): ?>
    <div class="ss-card ss-animate-fade-up">
        <div class="ss-card-body">
            <?= Component::emptyState([
                'icon'   => 'fa-user-graduate',
                'title'  => 'No internships yet',
                'desc'   => "Posting an internship is a great way to discover emerging talent and build your company's pipeline.",
                'action' => '<button type="button" class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#internshipModal"><i class="fas fa-plus"></i> Create Internship</button>'
            ]) ?>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($internships as $idx => $i):
            $sc = $statusMeta[$i['status'] ?? 'published'] ?? ['color' => 'soft', 'icon' => 'fa-circle', 'label' => ucfirst($i['status'] ?? 'draft')];
            $allowance = !empty($i['allowance']) ? number_format($i['allowance']) . ' RWF' : 'Unpaid';
            $deadlineStr = !empty($i['deadline']) ? date('M j, Y', strtotime($i['deadline'])) : 'Open';
            $desc = $i['description'] ?? '';
            $desc = strlen($desc) > 140 ? substr($desc, 0, 140) . '…' : $desc;
        ?>
        <div class="col-md-6 col-xl-4 ss-animate-fade-up ss-delay-<?= (string)(($idx % 4) + 1) ?>">
            <div class="ss-card ss-card-hover h-100">
                <div class="ss-card-body d-flex flex-column" style="gap:0.75rem;">
                    <div class="d-flex align-items-start gap-2">
                        <div class="ss-avatar ss-avatar-md" style="background:var(--ss-info-light);color:var(--ss-info);flex-shrink:0;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h4 style="margin:0;font-size:1rem;font-weight:700;" class="ss-clamp-2"><?= htmlspecialchars($i['title'] ?? 'Untitled Internship') ?></h4>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);margin-top:0.15rem;">
                                <?= htmlspecialchars($i['location'] ?? 'Remote') ?>
                            </div>
                        </div>
                        <span class="ss-badge ss-badge-<?= $sc['color'] ?>"><i class="fas <?= $sc['icon'] ?>"></i> <?= htmlspecialchars($sc['label']) ?></span>
                    </div>

                    <p style="font-size:0.82rem;color:var(--ss-text-2);line-height:1.5;margin:0;flex:1;"><?= htmlspecialchars($desc ?: 'No description provided.') ?></p>

                    <div class="d-flex flex-wrap gap-2" style="font-size:0.75rem;">
                        <span class="ss-chip"><i class="fas fa-clock me-1"></i><?= (int)($i['duration'] ?? 0) ?> <?= htmlspecialchars($i['duration_unit'] ?? 'months') ?></span>
                        <span class="ss-chip"><i class="fas fa-money-bill-wave me-1"></i><?= htmlspecialchars($allowance) ?></span>
                        <span class="ss-chip"><i class="fas fa-users me-1"></i><?= (int)($i['positions_available'] ?? 0) ?> spots</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2" style="border-top:1px solid var(--ss-border);">
                        <div style="font-size:0.75rem;color:var(--ss-text-3);">
                            <i class="fas fa-calendar"></i> <?= htmlspecialchars($deadlineStr) ?>
                            <?php if (!empty($i['applicant_count'])): ?>
                            · <i class="fas fa-users"></i> <?= (int)$i['applicant_count'] ?> applicants
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= URL::to('employer/applicants') ?>" class="ss-btn ss-btn-soft ss-btn-sm" title="View applicants"><i class="fas fa-users"></i></a>
                            <button type="button" class="ss-btn ss-btn-light ss-btn-sm" title="Edit"
                                onclick='openEditInternshipModal(<?= json_encode([
                                    "id" => (int)$i["id"],
                                    "title" => $i["title"] ?? "",
                                    "description" => $i["description"] ?? "",
                                    "requirements" => $i["requirements"] ?? "",
                                    "duration" => (int)($i["duration"] ?? 3),
                                    "duration_unit" => $i["duration_unit"] ?? "months",
                                    "allowance" => $i["allowance"] ?? "",
                                    "location" => $i["location"] ?? "",
                                    "deadline" => $i["deadline"] ?? "",
                                    "positions_available" => (int)($i["positions_available"] ?? 1),
                                    "status" => $i["status"] ?? "published",
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="<?= URL::to('employer/internships/' . (int)$i['id'] . '/delete') ?>" onsubmit="return confirm('Delete this internship? This cannot be undone.');" style="display:inline;">
                                <?= $csrfField ?? '' ?>
                                <button type="submit" class="ss-btn ss-btn-ghost ss-btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============== CREATE INTERNSHIP MODAL ============== -->
<div class="modal fade" id="internshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('employer/internships/store') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-graduate text-primary me-2"></i> Create Internship</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="internship_title" placeholder=" " required minlength="3">
                        <label for="internship_title">Internship Title <span class="req">*</span></label>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="internship_description">Description <span class="req">*</span></label>
                        <textarea name="description" id="internship_description" class="ss-textarea" required placeholder="Describe the internship program, what the intern will learn, and what they'll work on..."></textarea>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="internship_requirements">Requirements</label>
                        <textarea name="requirements" id="internship_requirements" class="ss-textarea" placeholder="One requirement per line.&#10;&#10;e.g.&#10;Currently enrolled in a university&#10;Basic knowledge of Python&#10;Available 3 days per week"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="duration" id="internship_duration" min="1" placeholder=" " required value="3">
                                <label for="internship_duration">Duration <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="duration_unit" id="internship_duration_unit" required>
                                    <?php foreach ($durationUnits as $k => $v): ?>
                                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="internship_duration_unit">Unit <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="allowance" id="internship_allowance" min="0" step="1000" placeholder=" ">
                                <label for="internship_allowance">Allowance (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="positions" id="internship_positions" min="1" placeholder=" " required value="1">
                                <label for="internship_positions">Positions <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="location" id="internship_location" placeholder=" " required>
                                <label for="internship_location">Location <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="date" name="deadline" id="internship_deadline" placeholder=" " min="<?= date('Y-m-d') ?>">
                                <label for="internship_deadline">Deadline</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Publish Internship</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============== EDIT INTERNSHIP MODAL ============== -->
<div class="modal fade" id="editInternshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editInternshipForm" action="" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary me-2"></i> Edit Internship</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="edit_internship_title" placeholder=" " required minlength="3">
                        <label for="edit_internship_title">Internship Title <span class="req">*</span></label>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_internship_description">Description <span class="req">*</span></label>
                        <textarea name="description" id="edit_internship_description" class="ss-textarea" required></textarea>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_internship_requirements">Requirements</label>
                        <textarea name="requirements" id="edit_internship_requirements" class="ss-textarea"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="duration" id="edit_internship_duration" min="1" placeholder=" " required>
                                <label for="edit_internship_duration">Duration <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="duration_unit" id="edit_internship_duration_unit" required>
                                    <?php foreach ($durationUnits as $k => $v): ?>
                                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="edit_internship_duration_unit">Unit <span class="req">*</span></label>
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
                                <label for="edit_internship_positions">Positions <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="location" id="edit_internship_location" placeholder=" " required>
                                <label for="edit_internship_location">Location <span class="req">*</span></label>
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
                                    <option value="published">Active / Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="closed">Closed</option>
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
<script>
function openEditInternshipModal(i) {
    document.getElementById('editInternshipForm').action = '<?= URL::to('employer/internships') ?>/' + i.id + '/update';
    document.getElementById('edit_internship_title').value = i.title || '';
    document.getElementById('edit_internship_description').value = i.description || '';
    document.getElementById('edit_internship_requirements').value = i.requirements || '';
    document.getElementById('edit_internship_duration').value = i.duration || 3;
    document.getElementById('edit_internship_duration_unit').value = i.duration_unit || 'months';
    document.getElementById('edit_internship_allowance').value = i.allowance || '';
    document.getElementById('edit_internship_positions').value = i.positions_available || 1;
    document.getElementById('edit_internship_location').value = i.location || '';
    document.getElementById('edit_internship_deadline').value = i.deadline ? i.deadline.substring(0, 10) : '';
    document.getElementById('edit_internship_status').value = i.status || 'published';
    new bootstrap.Modal(document.getElementById('editInternshipModal')).show();
}
</script>
