<?php
/**
 * Applications — Premium redesign (v3)
 *
 * Data passed from StudentController::applications():
 *   $applications — paginated array with keys: data[], total, current_page, per_page, last_page
 *
 * Each row in data[] has: id, position_title, company_name, type, status, applied_at, cover_letter
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'My Applications';

$applications = $applications ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 10, 'last_page' => 1];
$appList      = $applications['data'] ?? [];
$total        = (int)($applications['total'] ?? 0);
$currentPage  = (int)($applications['current_page'] ?? 1);
$lastPage     = (int)($applications['last_page'] ?? 1);

// Status metadata: badge color, icon, label
$statusMeta = [
    'pending'     => ['color' => 'warning', 'icon' => 'fa-clock',        'label' => 'Pending'],
    'reviewing'   => ['color' => 'info',    'icon' => 'fa-search',       'label' => 'Reviewing'],
    'reviewed'    => ['color' => 'info',    'icon' => 'fa-search',       'label' => 'Reviewed'],
    'shortlisted' => ['color' => 'primary', 'icon' => 'fa-star',         'label' => 'Shortlisted'],
    'interview'   => ['color' => 'info',    'icon' => 'fa-video',        'label' => 'Interview'],
    'offered'     => ['color' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Offered'],
    'accepted'    => ['color' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Accepted'],
    'rejected'    => ['color' => 'danger',  'icon' => 'fa-times-circle', 'label' => 'Rejected'],
    'withdrawn'   => ['color' => 'soft',    'icon' => 'fa-ban',          'label' => 'Withdrawn'],
];

// Count by status for tab badges
$counts = ['all' => count($appList)];
foreach (['pending' => 0, 'reviewed' => 0, 'accepted' => 0, 'rejected' => 0] as $key => $_) {
    $counts[$key] = 0;
}
foreach ($appList as $app) {
    $status = $app['status'] ?? 'pending';
    if (isset($counts[$status])) $counts[$status]++;
}

// Helper to render a status badge
$renderStatusBadge = function(string $status) use ($statusMeta): string {
    $m = $statusMeta[$status] ?? ['color' => 'soft', 'icon' => 'fa-circle', 'label' => ucfirst($status)];
    return '<span class="ss-badge ss-badge-' . $m['color'] . '"><i class="fas ' . $m['icon'] . '"></i> ' . htmlspecialchars($m['label']) . '</span>';
};
?>
<?= Component::pageHeader(
    'My Applications',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Applications</span>',
    '<a href="' . URL::to('student/jobs') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-search"></i> <span class="d-none d-md-inline">Find More Jobs</span></a>'
) ?>

<!-- ============== STAT CARDS ============== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-paper-plane', 'label' => 'Total Applications', 'count' => $total, 'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-clock',        'label' => 'Pending',           'count' => $counts['pending'] ?? 0,  'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle', 'label' => 'Accepted',          'count' => $counts['accepted'] ?? 0, 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-times-circle', 'label' => 'Rejected',          'count' => $counts['rejected'] ?? 0, 'color' => 'danger']) ?>
</div>

<!-- ============== TABS + TABLE ============== -->
<?php if (empty($appList)): ?>
    <div class="ss-card ss-animate-fade-up">
        <div class="ss-card-body">
            <?= Component::emptyState([
                'icon'   => 'fa-paper-plane',
                'title'  => 'No applications yet',
                'desc'   => "You haven't applied to any jobs or internships. Browse open positions and submit your first application today!",
                'action' => '<a href="' . URL::to('student/jobs') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-search"></i> Browse Jobs</a>'
            ]) ?>
        </div>
    </div>
<?php else: ?>
<div data-tabs class="ss-animate-fade-up">
    <div class="ss-tabs">
        <button class="ss-tab active" data-tab="#app-all"><i class="fas fa-list"></i> All <span class="count"><?= $counts['all'] ?></span></button>
        <button class="ss-tab" data-tab="#app-pending"><i class="fas fa-clock"></i> Pending <span class="count"><?= $counts['pending'] ?></span></button>
        <button class="ss-tab" data-tab="#app-reviewed"><i class="fas fa-search"></i> Reviewed <span class="count"><?= $counts['reviewed'] ?></span></button>
        <button class="ss-tab" data-tab="#app-accepted"><i class="fas fa-check-circle"></i> Accepted <span class="count"><?= $counts['accepted'] ?></span></button>
        <button class="ss-tab" data-tab="#app-rejected"><i class="fas fa-times-circle"></i> Rejected <span class="count"><?= $counts['rejected'] ?></span></button>
    </div>

    <!-- ALL -->
    <div class="ss-tab-pane active" id="app-all">
        <div class="ss-table-wrap" data-table>
            <div class="ss-table-toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search by position, company..." data-table-search>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export CSV</span></button>
                    <button class="ss-btn ss-btn-light ss-btn-sm" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>
                </div>
            </div>
            <div class="table-responsive-2">
                <table class="ss-table">
                    <thead>
                        <tr>
                            <th data-sort="position">Position <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="company">Company <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="applied">Applied <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                            <th class="no-sort text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appList as $app):
                            $status   = $app['status'] ?? 'pending';
                            $position = $app['position_title'] ?? 'Untitled position';
                            $company  = $app['company_name'] ?? 'N/A';
                        ?>
                        <tr>
                            <td>
                                <div class="table-avatar">
                                    <div class="avatar"><?= strtoupper(substr($position, 0, 1)) ?></div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($position) ?></div>
                                        <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars(ucfirst($app['type'] ?? 'job')) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($company) ?></td>
                            <td><span class="ss-badge ss-badge-info text-capitalize"><?= htmlspecialchars($app['type'] ?? 'job') ?></span></td>
                            <td style="font-size:0.8rem;color:var(--ss-text-2);">
                                <div><?= htmlspecialchars(date('M j, Y', strtotime($app['applied_at']))) ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars(timeAgo($app['applied_at'])) ?></div>
                            </td>
                            <td><?= $renderStatusBadge($status) ?></td>
                            <td class="text-end">
                                <a href="<?= URL::to('student/applications') ?>" class="ss-btn ss-btn-ghost ss-btn-sm" title="View details"><i class="fas fa-eye"></i></a>
                                <a href="<?= URL::to('student/jobs') ?>" class="ss-btn ss-btn-ghost ss-btn-sm" title="Similar jobs"><i class="fas fa-briefcase"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($lastPage > 1): ?>
            <div class="ss-table-pagination">
                <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= $total ?> applications</div>
                <div class="ss-pagination">
                    <?php if ($currentPage > 1): ?>
                        <a class="page-btn" href="<?= URL::to('student/applications?page=' . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php else: ?>
                        <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
                    <?php endif; ?>
                    <?php
                    $start = max(1, $currentPage - 2);
                    $end   = min($lastPage, $currentPage + 2);
                    if ($start > 1) {
                        echo '<a class="page-btn" href="' . URL::to('student/applications?page=1') . '">1</a>';
                        if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                    }
                    for ($p = $start; $p <= $end; $p++):
                    ?>
                        <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('student/applications?page=' . $p) ?>"><?= $p ?></a>
                    <?php endfor;
                    if ($end < $lastPage) {
                        if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                        echo '<a class="page-btn" href="' . URL::to('student/applications?page=' . $lastPage) . '">' . $lastPage . '</a>';
                    }
                    ?>
                    <?php if ($currentPage < $lastPage): ?>
                        <a class="page-btn" href="<?= URL::to('student/applications?page=' . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php else: ?>
                        <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PENDING / REVIEWED / ACCEPTED / REJECTED: client-filtered views of the same data -->
    <?php foreach (['pending' => 'Pending', 'reviewed' => 'Reviewed', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $key => $label): ?>
    <div class="ss-tab-pane" id="app-<?= $key ?>">
        <div class="ss-table-wrap">
            <div class="ss-table-toolbar">
                <div class="fw-semibold" style="font-size:0.9rem;">
                    <i class="fas fa-filter text-primary me-1"></i> Showing applications with status: <?= Component::badge($label, $statusMeta[$key]['color']) ?>
                </div>
                <div class="ms-auto" style="font-size:0.8rem;color:var(--ss-text-3);">
                    <?= $counts[$key] ?> application<?= $counts[$key] === 1 ? '' : 's' ?>
                </div>
            </div>
            <div class="table-responsive-2">
                <table class="ss-table">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Applied</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filtered = array_filter($appList, fn($a) => ($a['status'] ?? '') === $key);
                        if (empty($filtered)):
                        ?>
                        <tr><td colspan="4" style="text-align:center;padding:2.5rem;color:var(--ss-text-3);">
                            <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:0.4;"></i>
                            No applications with status "<?= htmlspecialchars($label) ?>".
                        </td></tr>
                        <?php else: foreach ($filtered as $app): ?>
                        <tr>
                            <td>
                                <div class="table-avatar">
                                    <div class="avatar"><?= strtoupper(substr($app['position_title'] ?? 'U', 0, 1)) ?></div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($app['position_title'] ?? 'Untitled') ?></div>
                                        <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars(ucfirst($app['type'] ?? 'job')) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($app['company_name'] ?? 'N/A') ?></td>
                            <td style="font-size:0.8rem;color:var(--ss-text-2);"><?= htmlspecialchars(date('M j, Y', strtotime($app['applied_at']))) ?></td>
                            <td><?= $renderStatusBadge($app['status'] ?? $key) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Application timeline / tips -->
<div class="ss-card ss-animate-fade-up mt-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-lightbulb text-primary"></i> Application Tips</h3>
    </div>
    <div class="ss-card-body">
        <div class="row g-3">
            <?php
            $tips = [
                ['icon' => 'fa-file-alt', 'color' => 'primary', 'title' => 'Tailor your resume', 'desc' => 'Customize your resume for each role using keywords from the job description.'],
                ['icon' => 'fa-pen',      'color' => 'success', 'title' => 'Write a cover letter', 'desc' => 'Applications with a cover letter are 53% more likely to get reviewed.'],
                ['icon' => 'fa-robot',    'color' => 'warning', 'title' => 'Boost AI score', 'desc' => 'Run the AI resume scorer to identify quick wins before you apply.'],
            ];
            foreach ($tips as $t):
            ?>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <div class="bg-soft-<?= $t['color'] ?>" style="width:36px;height:36px;border-radius:var(--ss-radius-sm);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas <?= $t['icon'] ?>"></i></div>
                    <div>
                        <div class="fw-semibold" style="font-size:0.85rem;"><?= htmlspecialchars($t['title']) ?></div>
                        <div style="font-size:0.78rem;color:var(--ss-text-2);"><?= htmlspecialchars($t['desc']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
