<?php
/**
 * Admin — Security Center
 * Data: $securityStats, $recentLogs, $flaggedUsers
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Security Center';
$sec = $securityStats ?? [];
?>
<?= Component::pageHeader('Security Center', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Security</span>') ?>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-exclamation-triangle', 'label' => 'Failed Logins (7d)', 'count' => $sec['failed_logins'] ?? 0, 'color' => 'danger']) ?>
    <?= Component::statCard(['icon' => 'fa-user-times', 'label' => 'Blocked Users', 'count' => $sec['blocked_users'] ?? 0, 'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-ban', 'label' => 'Suspended', 'count' => $sec['suspended_users'] ?? 0, 'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-users', 'label' => 'Active Sessions (24h)', 'count' => $sec['active_sessions'] ?? 0, 'color' => 'success']) ?>
</div>

<div class="row g-4 mb-4">
    <!-- Security Status -->
    <div class="col-lg-6">
        <div class="ss-card h-100">
            <div class="ss-card-header"><h3><i class="fas fa-shield-alt text-primary"></i> Protection Status</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><div style="width:36px;height:36px;border-radius:8px;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-check"></i></div><div style="flex:1;"><div style="font-size:0.82rem;font-weight:600;">CSRF Protection</div><div style="font-size:0.74rem;color:var(--ss-text-3);">Active on all forms</div></div><span class="ss-badge ss-badge-success">Active</span></div>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><div style="width:36px;height:36px;border-radius:8px;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-check"></i></div><div style="flex:1;"><div style="font-size:0.82rem;font-weight:600;">XSS Protection</div><div style="font-size:0.74rem;color:var(--ss-text-3);">All output escaped</div></div><span class="ss-badge ss-badge-success">Active</span></div>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><div style="width:36px;height:36px;border-radius:8px;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-check"></i></div><div style="flex:1;"><div style="font-size:0.82rem;font-weight:600;">SQL Injection Protection</div><div style="font-size:0.74rem;color:var(--ss-text-3);">PDO prepared statements</div></div><span class="ss-badge ss-badge-success">Active</span></div>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><div style="width:36px;height:36px;border-radius:8px;background:var(--ss-success-light);color:var(--ss-success);display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-check"></i></div><div style="flex:1;"><div style="font-size:0.82rem;font-weight:600;">Password Hashing</div><div style="font-size:0.74rem;color:var(--ss-text-3);">Bcrypt encryption</div></div><span class="ss-badge ss-badge-success">Active</span></div>
                    <div class="d-flex align-items-center gap-3 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-r-sm);"><div style="width:36px;height:36px;border-radius:8px;background:var(--ss-info-light);color:var(--ss-info);display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-lock"></i></div><div style="flex:1;"><div style="font-size:0.82rem;font-weight:600;">Two-Factor Authentication</div><div style="font-size:0.74rem;color:var(--ss-text-3);">Optional for admins</div></div><span class="ss-badge ss-badge-info">Optional</span></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Flagged Users -->
    <div class="col-lg-6">
        <div class="ss-card h-100">
            <div class="ss-card-header"><h3><i class="fas fa-user-times text-danger"></i> Flagged Users</h3><span class="ss-badge ss-badge-danger"><?= count($flaggedUsers ?? []) ?></span></div>
            <div class="ss-card-body" style="padding:0;">
                <?php if (!empty($flaggedUsers)): ?>
                <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
                    <table class="ss-table">
                        <thead><tr><th>User</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($flaggedUsers as $u):
                                $name = htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                                $sc = $u['status'] === 'banned' ? 'danger' : 'warning';
                            ?>
                            <tr>
                                <td><div class="table-avatar"><?= Component::avatar($name, null, 'xs') ?><div style="font-size:0.82rem;font-weight:600;"><?= $name ?></div></div></td>
                                <td style="font-size:0.78rem;"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td><?= Component::badge(ucfirst($u['status'] ?? 'suspended'), $sc) ?></td>
                                <td><a href="<?= URL::to('admin/users') ?>" class="ss-btn ss-btn-soft ss-btn-xs">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-check-circle', 'title' => 'No flagged users', 'desc' => 'All users are in good standing.']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Security Logs -->
<div class="ss-card">
    <div class="ss-card-header"><h3><i class="fas fa-history text-primary"></i> Recent Security Activity</h3><a href="<?= URL::to('admin/audit-logs') ?>" class="ss-btn ss-btn-ghost ss-btn-sm">View all</a></div>
    <div class="ss-card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="ss-table" data-table>
                <thead><tr><th>User</th><th>Action</th><th>Model</th><th>IP Address</th><th>Time</th></tr></thead>
                <tbody>
                    <?php foreach ($recentLogs ?? [] as $log):
                        $name = htmlspecialchars(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'System';
                        $ac = strtolower($log['action'] ?? 'view');
                        $aColor = ['create' => 'success', 'update' => 'info', 'delete' => 'danger', 'login' => 'primary'][$ac] ?? 'soft';
                    ?>
                    <tr>
                        <td><div class="table-avatar"><?= Component::avatar($name, null, 'xs') ?><div style="font-size:0.82rem;font-weight:600;"><?= $name ?></div></div></td>
                        <td><?= Component::badge(ucfirst($log['action'] ?? 'view'), $aColor) ?></td>
                        <td style="font-size:0.8rem;"><?= htmlspecialchars($log['model'] ?? '—') ?></td>
                        <td style="font-size:0.75rem;font-family:var(--ss-font-mono);"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                        <td style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, Y g:i a', strtotime($log['created_at'] ?? 'now'))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
