<?php
/**
 * Admin — Applications Management
 * Data: $applications (array), $stats (array with total/pending/reviewing/shortlisted/interview/offered/rejected)
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Applications';
$statusColors = ['pending' => 'warning', 'reviewing' => 'info', 'shortlisted' => 'info', 'interview' => 'info', 'offered' => 'success', 'rejected' => 'danger', 'withdrawn' => 'soft'];
?>
<?= Component::pageHeader('Applications Management', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Applications</span>') ?>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-folder-open', 'label' => 'Total', 'count' => $stats['total'] ?? 0, 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-clock', 'label' => 'Pending', 'count' => $stats['pending'] ?? 0, 'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-eye', 'label' => 'Reviewing', 'count' => $stats['reviewing'] ?? 0, 'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-handshake', 'label' => 'Offered', 'count' => $stats['offered'] ?? 0, 'color' => 'success']) ?>
</div>

<div class="ss-card">
    <div class="ss-card-header">
        <h3><i class="fas fa-folder-open text-primary"></i> All Applications</h3>
    </div>
    <div class="ss-card-body" style="padding:0;">
        <?php if (!empty($applications)): ?>
        <div class="table-responsive">
            <table class="ss-table" data-table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Position</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Applied</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app):
                        $name = htmlspecialchars(($app['first_name'] ?? '') . ' ' . ($app['last_name'] ?? ''));
                        $sc = $statusColors[$app['status'] ?? 'pending'] ?? 'soft';
                    ?>
                    <tr>
                        <td>
                            <div class="table-avatar">
                                <?= Component::avatar($name, null, 'sm') ?>
                                <div>
                                    <div style="font-size:0.82rem;font-weight:600;"><?= $name ?></div>
                                    <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($app['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($app['position_title'] ?? 'N/A') ?></td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($app['company_name'] ?? '—') ?></td>
                        <td><?= Component::badge(ucfirst($app['type'] ?? 'job'), 'soft') ?></td>
                        <td><?= Component::badge(ucfirst($app['status'] ?? 'pending'), $sc) ?></td>
                        <td style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, Y', strtotime($app['applied_at'] ?? 'now'))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-folder-open', 'title' => 'No applications', 'desc' => 'No job applications have been submitted yet.']) ?></div>
        <?php endif; ?>
    </div>
</div>
