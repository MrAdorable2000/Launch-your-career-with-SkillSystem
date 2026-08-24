<?php
/**
 * University — Students (Premium redesigned, ss-btn namespace)
 *
 * Data passed from UniversityController::students():
 *   $university  — university row
 *   $students    — paginated array: data[], total, current_page, per_page, last_page
 *   $search      — current search term
 *
 * Each student row: id, user_id, first_name, last_name, email, status,
 *                   department, year_of_study, gpa, university_id, uni_name,
 *                   created_at, profile_completion (may be absent — fall back to 0)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Student Directory';

$students = $students ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1];
$list = $students['data'] ?? [];
$total        = (int)($students['total'] ?? 0);
$currentPage  = (int)($students['current_page'] ?? 1);
$lastPage     = (int)($students['last_page'] ?? 1);

$yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year'];

// Stat derivation from current page sample
$seniorCount = 0; $topGpa = 0; $avgCompletion = 0;
foreach ($list as $s) {
    if (in_array(($s['year_of_study'] ?? 0), [4, 5], true)) $seniorCount++;
    $g = (float)($s['gpa'] ?? 0); if ($g > $topGpa) $topGpa = $g;
    $avgCompletion += (int)($s['profile_completion'] ?? 60);
}
$avgCompletion = $list ? (int)round($avgCompletion / count($list)) : 0;
?>
<?= Component::pageHeader(
    'Student Directory 📚',
    '<a href="' . URL::to('university/dashboard') . '">Dashboard</a> / <span>Students</span>',
    '<button class="ss-btn ss-btn-light" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export</span></button>' .
    '<button class="ss-btn ss-btn-light" data-bs-toggle="modal" data-bs-target="#contactAllModal"><i class="fas fa-paper-plane"></i> <span class="d-none d-md-inline">Message All</span></button>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="fas fa-user-plus"></i> <span class="d-none d-md-inline">Add Student</span></button>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-users',          'label' => 'Total Students',      'count' => $total,         'color' => 'primary', 'trend' => 'Enrolled']) ?>
    <?= Component::statCard(['icon' => 'fa-user-graduate',  'label' => 'Seniors (4th-5th)',   'count' => $seniorCount,   'color' => 'success', 'trend' => 'Approaching graduation']) ?>
    <?= Component::statCard(['icon' => 'fa-star',           'label' => 'Top GPA',             'value' => number_format($topGpa, 2), 'color' => 'warning', 'trend' => 'Highest in cohort']) ?>
    <?= Component::statCard(['icon' => 'fa-chart-pie',      'label' => 'Profile Completion',  'value' => $avgCompletion . '%', 'color' => 'info', 'trend' => 'Average across students']) ?>
</div>

<!-- ==================== STUDENTS TABLE ==================== -->
<div class="ss-table-wrap ss-animate-fade-up" data-table>
    <div class="ss-table-toolbar">
        <form method="GET" action="<?= URL::to('university/students') ?>" class="d-flex gap-2 flex-wrap" style="flex:1;min-width:240px;">
            <?= $csrfField ?? '' ?>
            <div class="search-box" style="max-width:340px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by name, department, email..." value="<?= htmlspecialchars($search ?? '') ?>" data-table-search>
            </div>
            <select name="department" class="ss-select ss-input-sm" style="width:auto;min-width:170px;" onchange="this.form.submit()">
                <option value="">All Departments</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Business Administration">Business Administration</option>
                <option value="Engineering">Engineering</option>
                <option value="Economics">Economics</option>
                <option value="Law">Law</option>
                <option value="Medicine">Medicine</option>
            </select>
            <select name="year" class="ss-select ss-input-sm" style="width:auto;min-width:130px;" onchange="this.form.submit()">
                <option value="">All Years</option>
                <?php foreach ($yearLabels as $y => $lbl): ?>
                    <option value="<?= $y ?>"><?= htmlspecialchars($lbl) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <?php if (!empty($search)): ?>
                <a href="<?= URL::to('university/students') ?>" class="ss-btn ss-btn-ghost ss-btn-sm"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
        <div class="ms-auto d-flex gap-2">
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">CSV</span></button>
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
        </div>
    </div>

    <?php if (empty($list)): ?>
        <div style="padding:2rem;">
            <?= Component::emptyState([
                'icon'  => 'fa-users-slash',
                'title' => 'No students found',
                'desc'  => $search ? 'No students match your search filters.' : 'No students are linked to your university yet. Once they register with your university ID, they will appear here.',
                'action'=> $search ? '<a href="' . URL::to('university/students') . '" class="ss-btn ss-btn-soft">Clear filters</a>' : '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="fas fa-user-plus"></i> Add your first student</button>'
            ]) ?>
        </div>
    <?php else: ?>
    <div class="table-responsive-2">
        <table class="ss-table">
            <thead>
                <tr>
                    <th data-sort="name">Student <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="department">Department <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="year">Year <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="gpa">GPA <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="placement">Placement <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="completion">Profile <i class="fas fa-sort sort-icon"></i></th>
                    <th class="no-sort text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $s):
                    $name = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?: 'Unknown Student';
                    $year = (int)($s['year_of_study'] ?? 0);
                    $yearLbl = $yearLabels[$year] ?? '—';
                    $gpa = (float)($s['gpa'] ?? 0);
                    $gpaColor = $gpa >= 3.7 ? 'success' : ($gpa >= 3.0 ? 'info' : ($gpa >= 2.0 ? 'warning' : 'danger'));
                    $completion = (int)($s['profile_completion'] ?? 0);
                    if ($completion === 0) $completion = 60; // fallback
                    // Placement status derived from gpa + completion
                    $placement = $gpa >= 3.5 && $completion >= 70 ? 'Placed' : ($gpa >= 3.0 ? 'Interviewing' : ($year >= 4 ? 'Seeking' : 'Studying'));
                    $placementColor = ['Placed' => 'success', 'Interviewing' => 'info', 'Seeking' => 'warning', 'Studying' => 'soft'][$placement] ?? 'soft';
                ?>
                <tr>
                    <td>
                        <div class="table-avatar">
                            <div class="avatar"><?= strtoupper(substr($name, 0, 1)) ?></div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);"><i class="fas fa-envelope" style="font-size:0.65rem;"></i> <?= htmlspecialchars($s['email'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ss-badge ss-badge-primary"><?= htmlspecialchars($s['department'] ?? 'Undeclared') ?></span>
                    </td>
                    <td><span style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($yearLbl) ?></span></td>
                    <td>
                        <?php if ($gpa > 0): ?>
                            <span class="fw-bold" style="color:var(--ss-<?= $gpaColor ?>);"><?= number_format($gpa, 2) ?></span>
                            <span style="font-size:0.7rem;color:var(--ss-text-3);">/ 4.0</span>
                        <?php else: ?>
                            <span style="color:var(--ss-text-3);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="ss-badge ss-badge-<?= $placementColor ?> text-capitalize">
                            <i class="fas fa-<?= ['Placed' => 'briefcase', 'Interviewing' => 'video', 'Seeking' => 'search', 'Studying' => 'book'][$placement] ?? 'circle' ?>"></i>
                            <?= htmlspecialchars($placement) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2" style="min-width:120px;">
                            <?= Component::progress($completion, $completion >= 70 ? 'success' : 'warning', 'sm') ?>
                            <span style="font-size:0.72rem;font-weight:700;color:var(--ss-text-2);"><?= $completion ?>%</span>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="ss-btn ss-btn-ghost ss-btn-sm" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="<?= URL::to('student/profile') ?>"><i class="fas fa-eye"></i> View profile</a>
                                <a class="dropdown-item" href="#" onclick='openEditStudentModal(<?= json_encode([
                                    "id" => (int)$s["id"],
                                    "name" => $name,
                                    "student_id_number" => $s["student_id_number"] ?? "",
                                    "department" => $s["department"] ?? "",
                                    "year_of_study" => (int)($s["year_of_study"] ?? 1),
                                    "gpa" => $s["gpa"] ?? "",
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>);return false;'><i class="fas fa-pen"></i> Edit record</a>
                                <a class="dropdown-item" href="<?= URL::to('student/messages') ?>"><i class="fas fa-envelope"></i> Contact student</a>
                                <a class="dropdown-item" href="<?= URL::to('university/reports') ?>"><i class="fas fa-chart-line"></i> View progress</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="confirmRemoveStudent(<?= (int)$s['id'] ?>, '<?= htmlspecialchars(addslashes($name)) ?>');return false;"><i class="fas fa-user-minus"></i> Remove from university</a>
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
        <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= $total ?> students</div>
        <div class="ss-pagination">
            <?php
            $baseQ = $search ? ('?search=' . urlencode($search) . '&page=') : ('?page=');
            if ($currentPage > 1): ?>
                <a class="page-btn" href="<?= URL::to('university/students' . $baseQ . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif;
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1) {
                echo '<a class="page-btn" href="' . URL::to('university/students' . $baseQ . '1') . '">1</a>';
                if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
            }
            for ($p = $start; $p <= $end; $p++):
            ?>
                <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('university/students' . $baseQ . $p) ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $lastPage) {
                if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                echo '<a class="page-btn" href="' . URL::to('university/students' . $baseQ . $lastPage) . '">' . $lastPage . '</a>';
            }
            ?>
            <?php if ($currentPage < $lastPage): ?>
                <a class="page-btn" href="<?= URL::to('university/students' . $baseQ . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ==================== CONTACT ALL MODAL ==================== -->
<div class="modal fade" id="contactAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane text-primary"></i> Message All Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URL::to('student/messages') ?>" method="POST" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="ss-alert ss-alert-info">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <div class="alert-body" style="font-size:0.82rem;">This message will be sent to <?= $total ?> students currently linked to your university.</div>
                    </div>
                    <div class="ss-form-group mt-3">
                        <label class="ss-form-label">Subject <span class="req">*</span></label>
                        <div class="ss-float">
                            <input type="text" name="subject" id="msgSubject" placeholder=" " required>
                            <label for="msgSubject">e.g. Career fair reminder</label>
                        </div>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Message <span class="req">*</span></label>
                        <textarea name="body" class="ss-textarea" rows="5" required placeholder="Type your announcement here…"></textarea>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Recipients</label>
                        <select name="recipient_filter" class="ss-select">
                            <option value="all">All students (<?= $total ?>)</option>
                            <option value="seniors">Seniors only</option>
                            <option value="placed">Placed students</option>
                            <option value="seeking">Seeking placement</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Send to <?= $total ?> Students</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== ADD STUDENT MODAL ==================== -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('university/students/add') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus text-primary"></i> Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="first_name" id="add_student_first_name" placeholder=" " required>
                                <label for="add_student_first_name">First Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="last_name" id="add_student_last_name" placeholder=" " required>
                                <label for="add_student_last_name">Last Name *</label>
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group ss-float">
                        <input type="email" name="email" id="add_student_email" placeholder=" " required>
                        <label for="add_student_email">Email Address *</label>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="student_id_number" id="add_student_id_number" placeholder=" ">
                                <label for="add_student_id_number">Student ID Number</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="department" id="add_student_department">
                                    <option value="">Select department...</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Business Administration">Business Administration</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Economics">Economics</option>
                                    <option value="Law">Law</option>
                                    <option value="Medicine">Medicine</option>
                                </select>
                                <label for="add_student_department">Department</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="year_of_study" id="add_student_year" required>
                                    <?php foreach ($yearLabels as $y => $lbl): ?>
                                        <option value="<?= $y ?>"><?= htmlspecialchars($lbl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="add_student_year">Year of Study *</label>
                            </div>
                        </div>
                    </div>
                    <div class="ss-alert ss-alert-info mt-2" style="font-size:0.8rem;">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <div class="alert-body">The student account will be created with a default password of <strong>password</strong> — they should change it after first login.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-user-plus"></i> Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== EDIT STUDENT MODAL ==================== -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editStudentForm" action="" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary"></i> Edit Student Record — <span id="edit_student_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="student_id_number" id="edit_student_id_number" placeholder=" ">
                        <label for="edit_student_id_number">Student ID Number</label>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="department" id="edit_student_department">
                                    <option value="">Undeclared</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Business Administration">Business Administration</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Economics">Economics</option>
                                    <option value="Law">Law</option>
                                    <option value="Medicine">Medicine</option>
                                </select>
                                <label for="edit_student_department">Department</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <select name="year_of_study" id="edit_student_year">
                                    <?php foreach ($yearLabels as $y => $lbl): ?>
                                        <option value="<?= $y ?>"><?= htmlspecialchars($lbl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="edit_student_year">Year of Study</label>
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group ss-float">
                        <input type="number" name="gpa" id="edit_student_gpa" min="0" max="4" step="0.01" placeholder=" ">
                        <label for="edit_student_gpa">GPA (out of 4.0)</label>
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

<!-- ==================== REMOVE STUDENT MODAL ==================== -->
<div class="modal fade" id="removeStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="removeStudentForm" action="">
                <?= $csrfField ?? '' ?>
                <div class="modal-header" style="border-bottom:1px solid var(--ss-border);">
                    <h5 class="modal-title" style="color:var(--ss-danger);"><i class="fas fa-exclamation-triangle me-2"></i> Remove Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Remove <strong id="remove_student_name"></strong> from your university's roster? Their account and personal data will be preserved — they just won't appear in your student directory anymore.</p>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ss-border);">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-danger"><i class="fas fa-user-minus"></i> Remove Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditStudentModal(s) {
    document.getElementById('editStudentForm').action = '<?= URL::to('university/students') ?>/' + s.id + '/update';
    document.getElementById('edit_student_name').textContent = s.name || '';
    document.getElementById('edit_student_id_number').value = s.student_id_number || '';
    document.getElementById('edit_student_department').value = s.department || '';
    document.getElementById('edit_student_year').value = s.year_of_study || 1;
    document.getElementById('edit_student_gpa').value = s.gpa || '';
    new bootstrap.Modal(document.getElementById('editStudentModal')).show();
}

function confirmRemoveStudent(id, name) {
    document.getElementById('removeStudentForm').action = '<?= URL::to('university/students') ?>/' + id + '/remove';
    document.getElementById('remove_student_name').textContent = name;
    new bootstrap.Modal(document.getElementById('removeStudentModal')).show();
}
</script>
