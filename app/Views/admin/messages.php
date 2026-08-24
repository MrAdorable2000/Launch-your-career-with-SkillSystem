<?php
/**
 * Admin — Messages Center
 * Data: $messages, $unread, $total
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Messages';
?>
<?= Component::pageHeader('Messages Center', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Messages</span>') ?>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-envelope', 'label' => 'Total Messages', 'count' => $total, 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-envelope-open', 'label' => 'Unread', 'count' => $unread, 'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-check', 'label' => 'Read', 'count' => $total - $unread, 'color' => 'success']) ?>
</div>

<div class="ss-card">
    <div class="ss-card-header">
        <h3><i class="fas fa-envelope text-primary"></i> All Messages</h3>
        <button class="ss-btn ss-btn-gradient ss-btn-sm" onclick="window.ssToast && ssToast.show('New message composer opened.', 'info')"><i class="fas fa-pen"></i> New Message</button>
    </div>
    <div class="ss-card-body" style="padding:0;">
        <?php if (!empty($messages)): ?>
        <div class="table-responsive">
            <table class="ss-table" data-table>
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg):
                        $sender = htmlspecialchars(($msg['sender_first'] ?? '') . ' ' . ($msg['sender_last'] ?? 'System'));
                        $receiver = htmlspecialchars(($msg['receiver_first'] ?? '') . ' ' . ($msg['receiver_last'] ?? 'User'));
                        $isUnread = empty($msg['read_at']);
                    ?>
                    <tr style="<?= $isUnread ? 'background:rgba(var(--ss-primary-rgb),0.04);' : '' ?>">
                        <td>
                            <div class="table-avatar">
                                <?= Component::avatar($sender, null, 'xs') ?>
                                <div style="font-size:0.82rem;font-weight:600;"><?= $sender ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="table-avatar">
                                <?= Component::avatar($receiver, null, 'xs') ?>
                                <div style="font-size:0.82rem;font-weight:600;"><?= $receiver ?></div>
                            </div>
                        </td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($msg['subject'] ?? '(no subject)') ?></td>
                        <td><?= $isUnread ? Component::badge('Unread', 'warning') : Component::badge('Read', 'success') ?></td>
                        <td style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, Y g:i a', strtotime($msg['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <button class="ss-btn ss-btn-ghost ss-btn-xs" onclick="window.ssToast && ssToast.show('Message opened.', 'info')"><i class="fas fa-eye"></i></button>
                            <button class="ss-btn ss-btn-ghost ss-btn-xs" style="color:var(--ss-danger);" onclick="window.ssToast && ssToast.show('Message archived.', 'warning')"><i class="fas fa-archive"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-envelope', 'title' => 'No messages', 'desc' => 'No messages have been sent yet.']) ?></div>
        <?php endif; ?>
    </div>
</div>
