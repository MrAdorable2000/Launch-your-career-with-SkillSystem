<?php
/**
 * Admin — Audit Logs (Premium redesigned, Stripe-quality)
 *
 * Data passed from AdminController::auditLogs():
 *   $logs         — array of rows: user_id, first_name, last_name, action, model,
 *                   model_id, ip_address, user_agent, old_values (JSON), new_values (JSON), created_at
 *   $current_page, $per_page, $total, $last_page — pagination metadata
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Audit Logs';

$logs       = $logs ?? [];
$total      = (int)($total ?? count($logs));
$perPage    = (int)($per_page ?? 25);
$currentPage = (int)($current_page ?? 1);
$lastPage   = (int)($last_page ?? 1);

// Action → color mapping
$actionColors = [
    'create' => 'success',
    'update' => 'info',
    'delete' => 'danger',
    'login'  => 'primary',
    'logout' => 'soft',
    'view'   => 'soft',
    'export' => 'warning',
    'import' => 'warning',
];

// Build filter options from log data
$actionOptions = []; $modelOptions = [];
foreach ($logs as $l) {
    $a = strtolower($l['action'] ?? '');
    $m = $l['model'] ?? '';
    if ($a && !isset($actionOptions[$a])) $actionOptions[$a] = ucfirst($a);
    if ($m && !isset($modelOptions[$m])) $modelOptions[$m] = ucfirst($m);
}
ksort($actionOptions); ksort($modelOptions);

// Stat metrics
$createCount = 0; $updateCount = 0; $deleteCount = 0;
foreach ($logs as $l) {
    $a = strtolower($l['action'] ?? '');
    if ($a === 'create') $createCount++;
    elseif ($a === 'update') $updateCount++;
    elseif ($a === 'delete') $deleteCount++;
}

// Helper to pretty-print JSON values
$prettyJson = function($raw) {
    if (empty($raw)) return null;
    $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
    if ($decoded === null) return null;
    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
};
?>
<?= Component::pageHeader(
    'Audit Logs 📜',
    '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Audit Logs</span>',
    '<button class="ss-btn ss-btn-light" data-table-export="csv"><i class="fas fa-file-csv"></i> <span class="d-none d-md-inline">Export</span></button>' .
    '<button class="ss-btn ss-btn-light" data-table-export="print"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>'
) ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-history',       'label' => 'Total Events',  'count' => $total,        'color' => 'primary', 'trend' => 'All time', 'trendUp' => true]) ?>
    <?= Component::statCard(['icon' => 'fa-plus-circle',   'label' => 'Creates',       'count' => $createCount,  'color' => 'success', 'trend' => 'On this page']) ?>
    <?= Component::statCard(['icon' => 'fa-pen',           'label' => 'Updates',       'count' => $updateCount,  'color' => 'info',    'trend' => 'On this page']) ?>
    <?= Component::statCard(['icon' => 'fa-trash',         'label' => 'Deletions',     'count' => $deleteCount,  'color' => 'danger',  'trend' => 'On this page']) ?>
</div>

<!-- ==================== LOGS TABLE ==================== -->
<div class="ss-table-wrap ss-animate-fade-up" data-table>
    <!-- Toolbar -->
    <div class="ss-table-toolbar">
        <form method="GET" action="<?= URL::to('admin/audit-logs') ?>" class="d-flex gap-2 flex-wrap" style="flex:1;min-width:240px;">
            <?= $csrfField ?? '' ?>
            <div class="search-box" style="max-width:340px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by user, IP, or model..." data-table-search>
            </div>
            <select name="action" class="ss-select" style="width:auto;min-width:130px;" onchange="this.form.submit()">
                <option value="">All Actions</option>
                <?php foreach ($actionOptions as $k => $v): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="model" class="ss-select" style="width:auto;min-width:130px;" onchange="this.form.submit()">
                <option value="">All Models</option>
                <?php foreach ($modelOptions as $k => $v): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    <?php if (empty($logs)): ?>
        <div style="padding:2rem;">
            <?= Component::emptyState([
                'icon'   => 'fa-history',
                'title'  => 'No audit entries',
                'desc'   => 'No log entries match your filters. Audit logs are recorded automatically as users interact with the system.',
                'action' => '<a href="' . URL::to('admin/audit-logs') . '" class="ss-btn ss-btn-soft">Clear filters</a>'
            ]) ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Model</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $idx => $l):
                    $name = trim(($l['first_name'] ?? '') . ' ' . ($l['last_name'] ?? '')) ?: 'System';
                    $ac   = strtolower($l['action'] ?? 'view');
                    $aColor = $actionColors[$ac] ?? 'soft';
                    $oldJson = $prettyJson($l['old_values'] ?? null);
                    $newJson = $prettyJson($l['new_values'] ?? null);
                    $hasDiff = !empty($oldJson) || !empty($newJson);
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?= Component::avatar($name, null, 'sm') ?>
                            <div>
                                <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($name) ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">User #<?= (int)($l['user_id'] ?? 0) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= Component::badge(ucfirst(htmlspecialchars($l['action'] ?? 'view')), $aColor) ?></td>
                    <td>
                        <div style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($l['model'] ?? '—') ?></div>
                        <div style="font-size:0.7rem;color:var(--ss-text-3);">#<?= (int)($l['model_id'] ?? 0) ?></div>
                    </td>
                    <td style="font-size:0.76rem;color:var(--ss-text-2);font-family:monospace;"><?= htmlspecialchars($l['ip_address'] ?? '—') ?></td>
                    <td style="font-size:0.78rem;color:var(--ss-text-2);">
                        <div><?= htmlspecialchars(date('M j, Y', strtotime($l['created_at'] ?? 'now'))) ?></div>
                        <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('g:i:s a', strtotime($l['created_at'] ?? 'now'))) ?></div>
                    </td>
                    <td class="text-end">
                        <?php if ($hasDiff): ?>
                            <button class="ss-btn ss-btn-ghost ss-btn-sm" data-bs-toggle="modal" data-bs-target="#diffModal<?= $idx ?>"><i class="fas fa-code"></i> View Changes</button>
                        <?php else: ?>
                            <span style="font-size:0.75rem;color:var(--ss-text-3);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($lastPage > 1): ?>
    <div class="ss-table-pagination">
        <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= $total ?> entries</div>
        <div class="ss-pagination">
            <?php
            $baseQ = '?page=';
            if ($currentPage > 1): ?>
                <a class="page-btn" href="<?= URL::to('admin/audit-logs' . $baseQ . ($currentPage - 1)) ?>"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
            <?php endif;
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1) {
                echo '<a class="page-btn" href="' . URL::to('admin/audit-logs' . $baseQ . '1') . '">1</a>';
                if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
            }
            for ($p = $start; $p <= $end; $p++):
            ?>
                <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('admin/audit-logs' . $baseQ . $p) ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $lastPage) {
                if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                echo '<a class="page-btn" href="' . URL::to('admin/audit-logs' . $baseQ . $lastPage) . '">' . $lastPage . '</a>';
            }
            ?>
            <?php if ($currentPage < $lastPage): ?>
                <a class="page-btn" href="<?= URL::to('admin/audit-logs' . $baseQ . ($currentPage + 1)) ?>"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ==================== DIFF VIEWER MODALS ==================== -->
<?php foreach ($logs as $idx => $l):
    $oldJson = $prettyJson($l['old_values'] ?? null);
    $newJson = $prettyJson($l['new_values'] ?? null);
    if (empty($oldJson) && empty($newJson)) continue;
    $name = trim(($l['first_name'] ?? '') . ' ' . ($l['last_name'] ?? '')) ?: 'System';
?>
<div class="modal fade" id="diffModal<?= $idx ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-code text-primary"></i>
                    Change Details — <?= htmlspecialchars($l['model'] ?? 'record') ?> #<?= (int)($l['model_id'] ?? 0) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?= Component::avatar($name, null, 'sm') ?>
                    <div>
                        <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($name) ?></div>
                        <div style="font-size:0.72rem;color:var(--ss-text-3);">
                            <?= htmlspecialchars($l['action'] ?? '') ?> · <?= htmlspecialchars(date('M j, Y g:i a', strtotime($l['created_at'] ?? 'now'))) ?>
                        </div>
                    </div>
                    <span class="ms-auto ss-badge ss-badge-soft" style="font-family:monospace;font-size:0.7rem;"><?= htmlspecialchars($l['ip_address'] ?? '—') ?></span>
                </div>

                <div class="row g-3">
                    <?php if (!empty($oldJson)): ?>
                    <div class="col-md-6">
                        <div class="ss-card" style="margin:0;">
                            <div class="ss-card-header" style="padding:0.75rem 1rem;">
                                <h3 style="font-size:0.85rem;color:var(--ss-danger);"><i class="fas fa-minus-circle"></i> Before</h3>
                            </div>
                            <div class="ss-card-body" style="padding:1rem;background:#0F172A;border-radius:0 0 12px 12px;">
                                <pre style="margin:0;color:#FCA5A5;font-size:0.74rem;font-family:monospace;white-space:pre-wrap;word-break:break-word;"><?= htmlspecialchars($oldJson) ?></pre>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($newJson)): ?>
                    <div class="col-md-6">
                        <div class="ss-card" style="margin:0;">
                            <div class="ss-card-header" style="padding:0.75rem 1rem;">
                                <h3 style="font-size:0.85rem;color:var(--ss-success);"><i class="fas fa-plus-circle"></i> After</h3>
                            </div>
                            <div class="ss-card-body" style="padding:1rem;background:#0F172A;border-radius:0 0 12px 12px;">
                                <pre style="margin:0;color:#86EFAC;font-size:0.74rem;font-family:monospace;white-space:pre-wrap;word-break:break-word;"><?= htmlspecialchars($newJson) ?></pre>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($l['user_agent'])): ?>
                <div class="ss-alert ss-alert-info mt-3">
                    <i class="fas fa-laptop alert-icon"></i>
                    <div class="alert-body">
                        <div class="alert-title" style="font-size:0.78rem;">User Agent</div>
                        <span style="font-family:monospace;font-size:0.72rem;word-break:break-all;"><?= htmlspecialchars($l['user_agent']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn ss-btn-ghost" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
