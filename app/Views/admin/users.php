<?php
/**
 * Admin — Manage Users (Premium redesigned, Stripe-quality)
 *
 * Data passed from AdminController::users():
 *   $users — paginated array: data[], total, current_page, per_page, last_page
 *   $search — current search term (string)
 *
 * Each row in data[]: id, first_name, last_name, email, role_id, role_name,
 *                     role_slug, status, avatar, phone, last_login_at, created_at
 *
 * AJAX form: POST /admin/users/{id}/status with field name="status" (active|inactive|suspended|banned)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Manage Users';

$users = $users ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 15, 'last_page' => 1];
$userList     = $users['data'] ?? [];
$total        = (int)($users['total'] ?? 0);
$currentPage  = (int)($users['current_page'] ?? 1);
$lastPage     = (int)($users['last_page'] ?? 1);
$perPage      = (int)($users['per_page'] ?? 15);

// Role badge color mapping
$roleColors = [
    'admin'      => 'danger',
    'student'    => 'primary',
    'employer'   => 'warning',
    'university' => 'info',
    'mentor'     => 'success',
];
$roleIcons = [
    'admin'      => 'fa-shield-alt',
    'student'    => 'fa-user-graduate',
    'employer'   => 'fa-building',
    'university' => 'fa-university',
    'mentor'     => 'fa-user-tie',
];
$statusColors = [
    'active'    => 'success',
    'inactive'  => 'soft',
    'suspended' => 'warning',
    'banned'    => 'danger',
];

// Derive sample-based metrics (controller does not pass aggregates)
$newThisMonth = 0; $activeCount = 0; $suspendedCount = 0;
foreach ($userList as $u) {
    if (strtotime($u['created_at'] ?? '') > strtotime('-30 days')) $newThisMonth++;
    if (($u['status'] ?? '') === 'active') $activeCount++;
    if (in_array($u['status'] ?? '', ['suspended', 'banned'], true)) $suspendedCount++;
}
?>
<?= Component::pageHeader(
    'Manage Users 👥',
    '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Users</span>',
    '<button class="ss-btn ss-btn-light" data-bs-toggle="modal" data-bs-target="#exportModal"><i class="fas fa-download"></i> <span class="d-none d-md-inline">Export</span></button>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#inviteModal"><i class="fas fa-user-plus"></i> <span class="d-none d-md-inline">Add User</span></button>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-users',         'label' => 'Total Users',      'count' => $total,          'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',  'label' => 'Active',           'count' => $activeCount,    'color' => 'success', 'trend' => 'On this page']) ?>
    <?= Component::statCard(['icon' => 'fa-ban',           'label' => 'Suspended/Banned', 'count' => $suspendedCount, 'color' => 'danger',  'trend' => 'Needs review']) ?>
    <?= Component::statCard(['icon' => 'fa-user-plus',     'label' => 'New (30 days)',    'count' => $newThisMonth,   'color' => 'info',    'trend' => 'On this page', 'trendUp' => true]) ?>
</div>

<!-- ==================== USERS TABLE ==================== -->
<div class="ss-table-wrap ss-animate-fade-up" data-table>
    <!-- Toolbar -->
    <div class="ss-table-toolbar">
        <form method="GET" action="<?= URL::to('admin/users') ?>" class="d-flex gap-2 flex-wrap" style="flex:1;min-width:240px;">
            <?= $csrfField ?? '' ?>
            <div class="search-box" style="max-width:340px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search ?? '') ?>" data-table-search>
            </div>
            <select name="role" class="ss-select" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <?php foreach (['admin' => 'Admin', 'student' => 'Student', 'employer' => 'Employer', 'university' => 'University', 'mentor' => 'Mentor'] as $slug => $name): ?>
                    <option value="<?= htmlspecialchars($slug) ?>" <?= (($search ?? '') === $slug) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="ss-select" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (['active', 'inactive', 'suspended', 'banned'] as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= (($search ?? '') === $st) ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <?php if (!empty($search)): ?>
                <a href="<?= URL::to('admin/users') ?>" class="ss-btn ss-btn-ghost ss-btn-sm"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
        <div class="ms-auto d-flex gap-2">
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">CSV</span></button>
            <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
        </div>
    </div>

    <?php if (empty($userList)): ?>
        <div style="padding:2rem;">
            <?= Component::emptyState([
                'icon'   => 'fa-users-slash',
                'title'  => 'No users found',
                'desc'   => $search ? 'No users match your search filters. Try adjusting your query.' : 'No users have registered yet. Once they do, they will appear here.',
                'action' => $search ? '<a href="' . URL::to('admin/users') . '" class="ss-btn ss-btn-soft">Clear filters</a>' : ''
            ]) ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="ss-table">
            <thead>
                <tr>
                    <th style="width:36px;"><label class="ss-check m-0"><input type="checkbox" id="selectAll"></label></th>
                    <th style="width:50px;">#</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $rowNum = ($currentPage - 1) * $perPage + 1; foreach ($userList as $u):
                    $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: 'Unknown';
                    $roleSlug = $u['role_slug'] ?? 'student';
                    $roleColor = $roleColors[$roleSlug] ?? 'soft';
                    $roleIcon  = $roleIcons[$roleSlug] ?? 'fa-user';
                    $status = $u['status'] ?? 'active';
                    $avatar = $u['avatar'] ?? '';
                    $isAdminUser = ((int)($u['role_id'] ?? 0) === 1 || ($u['role_slug'] ?? '') === 'admin' || ($u['role_name'] ?? '') === 'Administrator');
                ?>
                <tr data-user-id="<?= (int)$u['id'] ?>" data-is-admin="<?= $isAdminUser ? '1' : '0' ?>">
                    <td><label class="ss-check m-0"><input type="checkbox" class="row-check"></label></td>
                    <td style="font-weight:700;color:var(--ss-text-3);"><?= $rowNum++ ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?= Component::avatar($name, !empty($avatar) ? $avatar : null, 'sm') ?>
                            <div>
                                <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($name) ?></div>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);"><i class="fas fa-envelope" style="font-size:0.65rem;"></i> <?= htmlspecialchars($u['email'] ?? '') ?></div>
                                <?php if (!empty($u['phone'])): ?>
                                    <div style="font-size:0.7rem;color:var(--ss-text-3);"><i class="fas fa-phone" style="font-size:0.65rem;"></i> <?= htmlspecialchars($u['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ss-badge ss-badge-<?= $roleColor ?>">
                            <i class="fas <?= htmlspecialchars($roleIcon) ?>"></i>
                            <?= htmlspecialchars($u['role_name'] ?? ucfirst($roleSlug)) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isAdminUser): ?>
                            <span class="ss-badge ss-badge-danger" style="font-size:0.75rem;"><i class="fas fa-shield-alt"></i> Protected</span>
                        <?php else: ?>
                        <form method="POST" action="<?= URL::to('admin/users/' . (int)$u['id'] . '/status') ?>" class="user-status-form" data-user-status>
                            <?= $csrfField ?? '' ?>
                            <select class="ss-select user-status-select"
                                    name="status"
                                    data-user-id="<?= (int)$u['id'] ?>"
                                    style="width:auto;min-width:130px;font-size:0.78rem;font-weight:600;">
                                <?php foreach (['active', 'inactive', 'suspended', 'banned'] as $st):
                                    $sc = $statusColors[$st] ?? 'soft';
                                ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--ss-text-2);">
                        <?php if (!empty($u['last_login_at'])): ?>
                            <div><?= htmlspecialchars(date('M j, Y', strtotime($u['last_login_at']))) ?></div>
                            <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('g:i a', strtotime($u['last_login_at']))) ?></div>
                        <?php else: ?>
                            <span style="color:var(--ss-text-3);">Never</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--ss-text-2);"><?= htmlspecialchars(date('M j, Y', strtotime($u['created_at'] ?? 'now'))) ?></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="ss-btn ss-btn-ghost ss-btn-sm" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="<?= URL::to('admin/users?search=' . urlencode($u['email'] ?? '')) ?>"><i class="fas fa-eye"></i> View profile</a>
                                <a class="dropdown-item" href="#" onclick='openEditUserModal(<?= json_encode([
                                    "id" => (int)$u["id"],
                                    "first_name" => $u["first_name"] ?? "",
                                    "last_name" => $u["last_name"] ?? "",
                                    "email" => $u["email"] ?? "",
                                    "phone" => $u["phone"] ?? "",
                                    "role_slug" => $roleSlug,
                                    "is_admin" => $isAdminUser,
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>);return false;'><i class="fas fa-pen"></i> Edit user</a>
                                <a class="dropdown-item" href="#"><i class="fas fa-envelope"></i> Send email</a>
                                <?php if (!$isAdminUser): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-action="suspend" data-user-id="<?= (int)$u['id'] ?>"><i class="fas fa-pause text-warning"></i> Suspend</a>
                                <a class="dropdown-item" href="#" data-action="ban" data-user-id="<?= (int)$u['id'] ?>"><i class="fas fa-ban text-danger"></i> Ban</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="confirmDeleteUser(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['first_name'] ?? '') ?> <?= htmlspecialchars($u['last_name'] ?? '') ?>', '<?= htmlspecialchars($u['email'] ?? '') ?>');return false;"><i class="fas fa-trash"></i> Delete Permanently</a>
                                <?php else: ?>
                                <div class="dropdown-divider"></div>
                                <span class="dropdown-item-text text-muted" style="font-size:0.78rem;"><i class="fas fa-shield-alt"></i> Admin protected</span>
                                <?php endif; ?>
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
        <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= $total ?> users</div>
        <div class="ss-pagination">
            <?php
            $baseQ = !empty($search) ? ('?search=' . urlencode($search) . '&page=') : ('?page=');
            if ($currentPage > 1): ?>
                <a class="page-btn" href="<?= URL::to('admin/users' . $baseQ . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif;
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1) {
                echo '<a class="page-btn" href="' . URL::to('admin/users' . $baseQ . '1') . '">1</a>';
                if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
            }
            for ($p = $start; $p <= $end; $p++):
            ?>
                <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('admin/users' . $baseQ . $p) ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $lastPage) {
                if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                echo '<a class="page-btn" href="' . URL::to('admin/users' . $baseQ . $lastPage) . '">' . $lastPage . '</a>';
            }
            ?>
            <?php if ($currentPage < $lastPage): ?>
                <a class="page-btn" href="<?= URL::to('admin/users' . $baseQ . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ==================== INVITE USER MODAL ==================== -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus text-primary"></i> Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= URL::to('admin/users/create') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="first_name" id="newFirstName" placeholder=" " required>
                                <label for="newFirstName">First Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="last_name" id="newLastName" placeholder=" " required>
                                <label for="newLastName">Last Name *</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="ss-form-group ss-float">
                                <input type="email" name="email" id="newEmail" placeholder=" " required data-validate="email">
                                <label for="newEmail">Email Address *</label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="phone" id="newPhone" placeholder=" ">
                                <label for="newPhone">Phone</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Role *</label>
                                <select name="role" class="ss-select" required>
                                    <option value="">Select role...</option>
                                    <option value="student">🎓 Student</option>
                                    <option value="employer">🏢 Employer</option>
                                    <option value="university">🏛️ University</option>
                                    <option value="mentor">👨‍🏫 Mentor</option>
                                    <option value="admin">🛡️ Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" name="password" id="newPassword" placeholder=" " value="password">
                                <label for="newPassword">Password</label>
                            </div>
                        </div>
                    </div>
                    <div class="ss-alert ss-alert-info mt-3" style="font-size:0.8rem;">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <div class="alert-body">The user will be created with "active" status and email verified. Default password is <strong>password</strong> — the user should change it after first login.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-user-plus"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== EDIT USER MODAL ==================== -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen text-primary"></i> Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" data-validate>
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" id="edit_user_first_name" placeholder=" " required>
                                <label for="edit_user_first_name">First Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group ss-float">
                                <input type="text" id="edit_user_last_name" placeholder=" " required>
                                <label for="edit_user_last_name">Last Name *</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="ss-form-group ss-float">
                                <input type="email" id="edit_user_email" placeholder=" " required>
                                <label for="edit_user_email">Email Address *</label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="ss-form-group ss-float">
                                <input type="text" id="edit_user_phone" placeholder=" ">
                                <label for="edit_user_phone">Phone</label>
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group" id="edit_user_role_group">
                        <label class="ss-form-label">Role</label>
                        <select id="edit_user_role" class="ss-select">
                            <option value="student">🎓 Student</option>
                            <option value="employer">🏢 Employer</option>
                            <option value="university">🏛️ University</option>
                            <option value="mentor">👨‍🏫 Mentor</option>
                        </select>
                    </div>
                    <div id="edit_user_error" class="ss-alert ss-alert-danger mt-2" style="display:none;font-size:0.82rem;">
                        <i class="fas fa-exclamation-circle alert-icon"></i>
                        <div class="alert-body" id="edit_user_error_text"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient" id="edit_user_submit_btn"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openEditUserModal(u) {
    document.getElementById('edit_user_id').value = u.id;
    document.getElementById('edit_user_first_name').value = u.first_name || '';
    document.getElementById('edit_user_last_name').value = u.last_name || '';
    document.getElementById('edit_user_email').value = u.email || '';
    document.getElementById('edit_user_phone').value = u.phone || '';
    const roleGroup = document.getElementById('edit_user_role_group');
    if (u.is_admin) {
        roleGroup.style.display = 'none';
    } else {
        roleGroup.style.display = '';
        document.getElementById('edit_user_role').value = u.role_slug || 'student';
    }
    document.getElementById('edit_user_error').style.display = 'none';
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

document.getElementById('editUserForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('edit_user_submit_btn');
    const errBox = document.getElementById('edit_user_error');
    const errText = document.getElementById('edit_user_error_text');
    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const id = document.getElementById('edit_user_id').value;
    const formData = new FormData();
    formData.append('first_name', document.getElementById('edit_user_first_name').value);
    formData.append('last_name', document.getElementById('edit_user_last_name').value);
    formData.append('email', document.getElementById('edit_user_email').value);
    formData.append('phone', document.getElementById('edit_user_phone').value);
    formData.append('role', document.getElementById('edit_user_role').value);
    formData.append('_token', document.querySelector('input[name="_token"]')?.value || '');

    try {
        const res = await fetch('<?= URL::to('admin/users/') ?>' + id + '/update', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editUserModal'))?.hide();
            if (window.ssToast) ssToast.show(data.message || 'User updated', 'success');
            setTimeout(() => window.location.reload(), 700);
        } else {
            errText.textContent = data.message || 'Failed to update user';
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        }
    } catch (err) {
        errText.textContent = 'Network error. Please try again.';
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
    }
});
</script>

<!-- ==================== EXPORT MODAL ==================== -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-download text-primary"></i> Export Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.85rem;color:var(--ss-text-2);">Export <?= $total ?> users to a CSV file.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="ss-btn ss-btn-soft" data-table-export="csv"><i class="fas fa-file-csv"></i> Export CSV</button>
                    <button class="ss-btn ss-btn-soft" data-table-export="print"><i class="fas fa-print"></i> Print List</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn ss-btn-ghost" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Inline status change -> POST via fetch
    document.querySelectorAll('.user-status-select').forEach(function(sel) {
        sel.addEventListener('change', async function() {
            const form = this.closest('form[data-user-status]');
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
                    window.showToast(json.message || 'Status updated', json.success ? 'success' : 'danger');
                }
            } catch (e) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Failed to update status', 'danger');
                }
            }
        });
    });

    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(c => c.checked = selectAll.checked);
        });
    }
})();
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid var(--ss-border);">
                <h5 class="modal-title" style="color:var(--ss-danger);"><i class="fas fa-exclamation-triangle me-2"></i> Delete User Permanently</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ss-alert ss-alert-danger mb-3">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-body">
                        <div class="alert-title">Warning: This action cannot be undone!</div>
                        Deleting a user will permanently remove them and all their data including:
                    </div>
                </div>
                <ul style="font-size:0.85rem;color:var(--ss-text-2);padding-left:1.2rem;">
                    <li>Profile (student/employer/mentor/university records)</li>
                    <li>All job applications</li>
                    <li>Messages and notifications</li>
                    <li>Portfolios, certificates, resumes</li>
                    <li>Discussion posts and comments</li>
                    <li>Payments and subscriptions</li>
                    <li>Activity and audit logs (anonymized)</li>
                </ul>
                <div class="d-flex align-items-center gap-3 p-3 mt-3" style="background:var(--ss-surface-2);border-radius:var(--ss-r);">
                    <div class="ss-avatar ss-avatar-md" id="deleteUserAvatar" style="background:var(--ss-danger);">?</div>
                    <div>
                        <div style="font-weight:700;font-size:0.95rem;" id="deleteUserName">User Name</div>
                        <div style="font-size:0.78rem;color:var(--ss-text-3);" id="deleteUserEmail">user@email.com</div>
                    </div>
                </div>
                <div class="ss-form-group mt-3">
                    <label class="ss-form-label">Type <strong>DELETE</strong> to confirm:</label>
                    <input type="text" class="ss-input" id="deleteConfirmInput" placeholder="Type DELETE here" oninput="document.getElementById('deleteConfirmBtn').disabled = (this.value !== 'DELETE');">
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ss-border);">
                <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ss-btn ss-btn-danger" id="deleteConfirmBtn" disabled onclick="executeUserDelete()"><i class="fas fa-trash"></i> Delete Permanently</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteUserId = 0;

function confirmDeleteUser(id, name, email) {
    deleteUserId = id;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteUserEmail').textContent = email;
    document.getElementById('deleteUserAvatar').textContent = name.charAt(0).toUpperCase();
    document.getElementById('deleteConfirmInput').value = '';
    document.getElementById('deleteConfirmBtn').disabled = true;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

async function executeUserDelete() {
    const btn = document.getElementById('deleteConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const res = await fetch('<?= URL::to('admin/users/') ?>' + deleteUserId + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ _token: csrfToken }),
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'))?.hide();
            if (window.ssToast) ssToast.show(data.message || 'User deleted permanently', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.ssToast) ssToast.show(data.message || 'Failed to delete user', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Delete Permanently';
        }
    } catch (e) {
        if (window.ssToast) ssToast.show('Network error', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete Permanently';
    }
}
</script>
