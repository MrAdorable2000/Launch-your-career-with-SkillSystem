<?php
/**
 * Admin — Certificates Management
 * Data: $certificates (array), $stats (array with total/verified/pending)
 */
use App\Helpers\URL;
use App\Helpers\Component;
$pageTitle = 'Certificates';
?>
<?= Component::pageHeader('Certificates Management', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Certificates</span>') ?>

<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-certificate', 'label' => 'Total', 'count' => $stats['total'] ?? 0, 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle', 'label' => 'Verified', 'count' => $stats['verified'] ?? 0, 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-clock', 'label' => 'Pending', 'count' => $stats['pending'] ?? 0, 'color' => 'warning']) ?>
</div>

<div class="ss-card">
    <div class="ss-card-header">
        <h3><i class="fas fa-certificate text-primary"></i> All Certificates</h3>
    </div>
    <div class="ss-card-body" style="padding:0;">
        <?php if (!empty($certificates)): ?>
        <div class="table-responsive">
            <table class="ss-table" data-table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Certificate</th>
                        <th>Issued By</th>
                        <th>Cert Number</th>
                        <th>Verification Code</th>
                        <th>Status</th>
                        <th>Issue Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $cert):
                        $name = htmlspecialchars(($cert['first_name'] ?? '') . ' ' . ($cert['last_name'] ?? ''));
                    ?>
                    <tr>
                        <td>
                            <div class="table-avatar">
                                <?= Component::avatar($name, null, 'sm') ?>
                                <div>
                                    <div style="font-size:0.82rem;font-weight:600;"><?= $name ?></div>
                                    <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($cert['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($cert['title'] ?? '') ?></td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($cert['issuing_organization'] ?? '') ?></td>
                        <td style="font-size:0.78rem;font-family:var(--ss-font-mono);"><?= htmlspecialchars($cert['certificate_number'] ?? '—') ?></td>
                        <td style="font-size:0.75rem;font-family:var(--ss-font-mono);"><?= htmlspecialchars($cert['verification_code'] ?? '—') ?></td>
                        <td><?= !empty($cert['verified']) ? Component::badge('Verified', 'success') : Component::badge('Pending', 'warning') ?></td>
                        <td style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars($cert['issued_date'] ? date('M j, Y', strtotime($cert['issued_date'])) : '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-certificate', 'title' => 'No certificates', 'desc' => 'No certificates have been issued yet.']) ?></div>
        <?php endif; ?>
    </div>
</div>
