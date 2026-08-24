<?php
/**
 * Employer — Freelance Projects (premium redesign v3)
 *
 * Data passed from EmployerController::freelance():
 *   $projects — array of freelance project rows, each with:
 *     title, description, budget_min, budget_max, skills_required, deadline,
 *     status, bid_count
 *
 * Create form submits via POST /employer/freelance/store
 * Controller reads: title, description, budget_min (getInt), budget_max (getInt),
 *                   skills_required, deadline
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Freelance Projects';

$projects = $projects ?? [];

// Derived stats
$totalProj   = count($projects);
$openProj    = count(array_filter($projects, fn($p) => ($p['status'] ?? '') === 'open'));
$totalBids   = 0; $totalBudget = 0;
foreach ($projects as $p) {
    $totalBids   += (int)($p['bid_count'] ?? 0);
    $totalBudget += (int)($p['budget_max'] ?: ($p['budget_min'] ?? 0));
}

$statusMeta = [
    'open'      => ['color' => 'success', 'icon' => 'fa-circle',          'label' => 'Open'],
    'in_progress' => ['color' => 'info',  'icon' => 'fa-spinner',         'label' => 'In Progress'],
    'completed' => ['color' => 'primary', 'icon' => 'fa-check-circle',    'label' => 'Completed'],
    'closed'    => ['color' => 'danger',  'icon' => 'fa-lock',            'label' => 'Closed'],
];
?>
<?= Component::pageHeader(
    'Freelance Projects',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <span>Freelance</span>',
    '<button type="button" class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#freelanceModal"><i class="fas fa-plus"></i> <span class="d-none d-md-inline">Post Project</span></button>'
) ?>

<!-- ============== STAT CARDS ============== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-laptop-code', 'label' => 'Total Projects',  'count' => $totalProj,    'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-circle',      'label' => 'Open Projects',   'count' => $openProj,     'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-gavel',       'label' => 'Total Bids',      'count' => $totalBids,    'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-money-bill-wave', 'label' => 'Committed Budget', 'value' => $totalBudget > 0 ? number_format($totalBudget) . ' RWF' : '—', 'color' => 'warning']) ?>
</div>

<!-- ============== FREELANCE PROJECT CARD GRID ============== -->
<?php if (empty($projects)): ?>
    <div class="ss-card ss-animate-fade-up">
        <div class="ss-card-body">
            <?= Component::emptyState([
                'icon'   => 'fa-laptop-code',
                'title'  => 'No freelance projects yet',
                'desc'   => "Post a freelance project to hire talented freelancers for short-term work, gigs and one-off tasks.",
                'action' => '<button type="button" class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#freelanceModal"><i class="fas fa-plus"></i> Post a Project</button>'
            ]) ?>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($projects as $idx => $p):
            $sc = $statusMeta[$p['status'] ?? 'open'] ?? ['color' => 'soft', 'icon' => 'fa-circle', 'label' => ucfirst($p['status'] ?? 'open')];
            $budget = '';
            if (!empty($p['budget_min'])) {
                $budget = number_format($p['budget_min']);
                if (!empty($p['budget_max'])) $budget .= ' – ' . number_format($p['budget_max']);
                $budget .= ' RWF';
            } else {
                $budget = 'Negotiable';
            }
            $deadlineStr = !empty($p['deadline']) ? date('M j, Y', strtotime($p['deadline'])) : 'Flexible';
            $skills = !empty($p['skills_required']) ? array_slice(array_map('trim', explode(',', $p['skills_required'])), 0, 4) : [];
            $desc = $p['description'] ?? '';
            $desc = strlen($desc) > 140 ? substr($desc, 0, 140) . '…' : $desc;
        ?>
        <div class="col-md-6 col-xl-4 ss-animate-fade-up ss-delay-<?= (string)(($idx % 4) + 1) ?>">
            <div class="ss-card ss-card-hover h-100">
                <div class="ss-card-body d-flex flex-column" style="gap:0.75rem;">
                    <div class="d-flex align-items-start gap-2">
                        <div class="ss-avatar ss-avatar-md" style="background:var(--ss-success-light);color:var(--ss-success);flex-shrink:0;">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h4 style="margin:0;font-size:1rem;font-weight:700;" class="ss-clamp-2"><?= htmlspecialchars($p['title'] ?? 'Untitled Project') ?></h4>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);margin-top:0.15rem;">
                                <i class="fas fa-gavel"></i> <?= (int)($p['bid_count'] ?? 0) ?> bids
                            </div>
                        </div>
                        <span class="ss-badge ss-badge-<?= $sc['color'] ?>"><i class="fas <?= $sc['icon'] ?>"></i> <?= htmlspecialchars($sc['label']) ?></span>
                    </div>

                    <p style="font-size:0.82rem;color:var(--ss-text-2);line-height:1.5;margin:0;flex:1;"><?= htmlspecialchars($desc ?: 'No description provided.') ?></p>

                    <?php if (!empty($skills)): ?>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($skills as $skill): ?>
                            <span class="ss-chip"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-2" style="font-size:0.75rem;">
                        <span class="ss-chip"><i class="fas fa-money-bill-wave me-1"></i><?= htmlspecialchars($budget) ?></span>
                        <span class="ss-chip"><i class="fas fa-calendar me-1"></i><?= htmlspecialchars($deadlineStr) ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2" style="border-top:1px solid var(--ss-border);">
                        <div style="font-size:0.75rem;color:var(--ss-text-3);">
                            <i class="fas fa-gavel"></i> <?= (int)($p['bid_count'] ?? 0) ?> bids
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= URL::to('employer/freelance') ?>" class="ss-btn ss-btn-soft ss-btn-sm" title="View bids"><i class="fas fa-eye"></i></a>
                            <button type="button" class="ss-btn ss-btn-light ss-btn-sm" title="Edit"
                                onclick='openEditProjectModal(<?= json_encode([
                                    "id" => (int)$p["id"],
                                    "title" => $p["title"] ?? "",
                                    "description" => $p["description"] ?? "",
                                    "budget_min" => $p["budget_min"] ?? "",
                                    "budget_max" => $p["budget_max"] ?? "",
                                    "skills_required" => $p["skills_required"] ?? "",
                                    "deadline" => $p["deadline"] ?? "",
                                    "status" => $p["status"] ?? "open",
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" action="<?= URL::to('employer/freelance/' . (int)$p['id'] . '/delete') ?>" onsubmit="return confirm('Delete this project? This cannot be undone.');" style="display:inline;">
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

<!-- ============== POST FREELANCE PROJECT MODAL ============== -->
<div class="modal fade" id="freelanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('employer/freelance/store') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-laptop-code text-success me-2"></i> Post a Freelance Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="project_title" placeholder=" " required minlength="3">
                        <label for="project_title">Project Title <span class="req">*</span></label>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="project_description">Description <span class="req">*</span></label>
                        <textarea name="description" id="project_description" class="ss-textarea" required placeholder="Describe the project scope, deliverables, milestones, and what you need the freelancer to do..."></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="budget_min" id="project_budget_min" min="0" step="1000" placeholder=" ">
                                <label for="project_budget_min">Budget Min (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="budget_max" id="project_budget_max" min="0" step="1000" placeholder=" ">
                                <label for="project_budget_max">Budget Max (RWF)</label>
                            </div>
                        </div>
                    </div>

                    <div class="ss-form-group ss-float">
                        <input type="text" name="skills_required" id="project_skills" placeholder=" ">
                        <label for="project_skills">Skills Required</label>
                        <div class="ss-form-hint" style="position:relative;">Comma-separated — e.g. <em>PHP, React, UI/UX Design</em></div>
                    </div>

                    <div class="ss-form-group ss-float">
                        <input type="date" name="deadline" id="project_deadline" placeholder=" " min="<?= date('Y-m-d') ?>">
                        <label for="project_deadline">Project Deadline</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Post Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============== EDIT PROJECT MODAL ============== -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editProjectForm" action="" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-success me-2"></i> Edit Freelance Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="edit_project_title" placeholder=" " required minlength="3">
                        <label for="edit_project_title">Project Title <span class="req">*</span></label>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="edit_project_description">Description <span class="req">*</span></label>
                        <textarea name="description" id="edit_project_description" class="ss-textarea" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="budget_min" id="edit_project_budget_min" min="0" step="1000" placeholder=" ">
                                <label for="edit_project_budget_min">Budget Min (RWF)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="number" name="budget_max" id="edit_project_budget_max" min="0" step="1000" placeholder=" ">
                                <label for="edit_project_budget_max">Budget Max (RWF)</label>
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group ss-float">
                        <input type="text" name="skills_required" id="edit_project_skills" placeholder=" ">
                        <label for="edit_project_skills">Skills Required</label>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="date" name="deadline" id="edit_project_deadline" placeholder=" ">
                                <label for="edit_project_deadline">Deadline</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="status" id="edit_project_status" required>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <label for="edit_project_status">Status</label>
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
function openEditProjectModal(p) {
    document.getElementById('editProjectForm').action = '<?= URL::to('employer/freelance') ?>/' + p.id + '/update';
    document.getElementById('edit_project_title').value = p.title || '';
    document.getElementById('edit_project_description').value = p.description || '';
    document.getElementById('edit_project_budget_min').value = p.budget_min || '';
    document.getElementById('edit_project_budget_max').value = p.budget_max || '';
    document.getElementById('edit_project_skills').value = p.skills_required || '';
    document.getElementById('edit_project_deadline').value = p.deadline ? p.deadline.substring(0, 10) : '';
    document.getElementById('edit_project_status').value = p.status || 'open';
    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}
</script>
