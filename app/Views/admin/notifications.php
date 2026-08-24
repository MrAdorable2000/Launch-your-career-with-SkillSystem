<?php
/**
 * Admin — Notifications Center
 * Data: $notifications, $unread, $total
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Notifications';
$typeIcons = [
    'application' => 'fa-folder-open', 'interview' => 'fa-calendar', 'offer' => 'fa-handshake',
    'message' => 'fa-envelope', 'system' => 'fa-cog', 'badge' => 'fa-medal',
    'certificate' => 'fa-certificate', 'mentorship' => 'fa-chalkboard-teacher', 'forum' => 'fa-comments',
];
$typeColors = [
    'application' => 'primary', 'interview' => 'info', 'offer' => 'success',
    'message' => 'info', 'system' => 'warning', 'badge' => 'warning',
    'certificate' => 'accent', 'mentorship' => 'primary', 'forum' => 'info',
];
?>
<?= Component::pageHeader('Notifications Center', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Notifications</span>') ?>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-bell', 'label' => 'Total', 'count' => $total, 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-bell-slash', 'label' => 'Unread', 'count' => $unread, 'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-check', 'label' => 'Read', 'count' => $total - $unread, 'color' => 'success']) ?>
</div>

<div class="ss-card">
    <div class="ss-card-header">
        <h3><i class="fas fa-bell text-primary"></i> All Notifications</h3>
        <button class="ss-btn ss-btn-soft ss-btn-sm" onclick="window.ssToast && ssToast.show('All notifications marked as read.', 'success')"><i class="fas fa-check-double"></i> Mark All Read</button>
    </div>
    <div class="ss-card-body" style="padding:0;">
        <?php if (!empty($notifications)): ?>
        <div style="max-height:600px;overflow-y:auto;">
            <?php foreach ($notifications as $n):
                $isUnread = empty($n['read_at']);
                $icon = $typeIcons[$n['type'] ?? 'system'] ?? 'fa-bell';
                $color = $typeColors[$n['type'] ?? 'system'] ?? 'soft';
                $userName = htmlspecialchars(($n['first_name'] ?? '') . ' ' . ($n['last_name'] ?? ''));
            ?>
            <div class="d-flex gap-3 p-3 border-bottom" style="border-color:var(--ss-border) !important;<?= $isUnread ? 'background:rgba(var(--ss-primary-rgb),0.04);' : '' ?>">
                <div style="width:40px;height:40px;border-radius:var(--ss-r-sm);background:var(--ss-<?= $color ?>-light);color:var(--ss-<?= $color ?>);display:inline-flex;align-items:center;justify-content:center;font-size:0.95rem;flex-shrink:0;">
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($n['title'] ?? '') ?></span>
                        <?= Component::badge(ucfirst($n['type'] ?? 'system'), $color) ?>
                        <?php if ($isUnread): ?><span style="width:8px;height:8px;border-radius:50%;background:var(--ss-primary);"></span><?php endif; ?>
                    </div>
                    <div style="font-size:0.8rem;color:var(--ss-text-2);margin-top:2px;"><?= htmlspecialchars($n['message'] ?? '') ?></div>
                    <div style="font-size:0.72rem;color:var(--ss-text-3);margin-top:4px;">
                        <?php if ($userName): ?><i class="fas fa-user me-1"></i><?= $userName ?> · <?php endif; ?>
                        <i class="fas fa-clock me-1"></i><?= htmlspecialchars(date('M j, Y g:i a', strtotime($n['created_at'] ?? 'now'))) ?>
                    </div>
                </div>
                <?php if ($isUnread): ?>
                <button class="ss-btn ss-btn-ghost ss-btn-xs" onclick="window.ssToast && ssToast.show('Marked as read.', 'success')"><i class="fas fa-check"></i></button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-bell-slash', 'title' => 'No notifications', 'desc' => 'No notifications have been sent yet.']) ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Send Notification Card -->
<div class="ss-card mt-4">
    <div class="ss-card-header"><h3><i class="fas fa-paper-plane text-primary"></i> Send Broadcast Notification</h3></div>
    <div class="ss-card-body">
        <form onsubmit="event.preventDefault(); window.ssToast && ssToast.show('Notification sent to all users!', 'success');">
            <div class="ss-form-group">
                <label class="ss-form-label">Target Audience</label>
                <select class="ss-select">
                    <option value="all">All Users</option>
                    <option value="students">All Students</option>
                    <option value="employers">All Employers</option>
                    <option value="universities">All Universities</option>
                    <option value="mentors">All Mentors</option>
                </select>
            </div>
            <div class="ss-form-group">
                <label class="ss-form-label">Title</label>
                <input type="text" class="ss-input" placeholder="Notification title" required>
            </div>
            <div class="ss-form-group">
                <label class="ss-form-label">Message</label>
                <textarea class="ss-textarea" placeholder="Notification message" required></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Send Notification</button>
                <button type="reset" class="ss-btn ss-btn-light">Clear</button>
            </div>
        </form>
    </div>
</div>
