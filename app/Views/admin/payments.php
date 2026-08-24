<?php
/**
 * Admin — Payments Management Center
 * Data: $payments, $totalRevenue, $completed, $pending, $failed, $refunded, $total,
 *       $settings, $paymentMethods, $plans, $csrfField
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;

$pageTitle = 'Payments';
$csrf = $csrfField ?? '';
?>
<?= Component::pageHeader('Payments & Billing', '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Payments</span>') ?>

<!-- ==================== STAT CARDS ==================== -->
<div class="ss-stats-grid mb-4">
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-success"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value"><?= number_format($totalRevenue, 0) ?></div>
        <div class="stat-label">Total Revenue (RWF)</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-primary"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?= (int)$completed ?></div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?= (int)$pending ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-danger"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value"><?= (int)$failed ?></div>
        <div class="stat-label">Failed</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-info"><i class="fas fa-undo"></i></div>
        <div class="stat-value"><?= (int)$refunded ?></div>
        <div class="stat-label">Refunded</div>
    </div>
</div>

<!-- ==================== PAYMENT METHODS ==================== -->
<div class="ss-card mb-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-credit-card text-primary"></i> Payment Methods</h3>
        <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-cog"></i> Configure in Settings</a>
    </div>
    <div class="ss-card-body">
        <div class="row g-3">
            <?php foreach ($paymentMethods as $key => $method): ?>
            <div class="col-md-6 col-lg-4">
                <div class="ss-card-hover" style="border:1px solid var(--ss-border);border-radius:var(--ss-r-lg);padding:1.25rem;height:100%;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:48px;height:48px;border-radius:var(--ss-r-sm);background:var(--ss-<?= $method['color'] ?>-light);color:var(--ss-<?= $method['color'] ?>);display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                            <i class="<?= htmlspecialchars($method['icon']) ?>"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:0.95rem;"><?= htmlspecialchars($method['name']) ?></div>
                            <?= $method['enabled']
                                ? '<span class="ss-badge ss-badge-success"><i class="fas fa-check"></i> Active</span>'
                                : '<span class="ss-badge ss-badge-soft"><i class="fas fa-times"></i> Inactive</span>' ?>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <?php foreach ($method['config'] as $cfgLabel => $cfgVal): ?>
                        <div class="d-flex justify-content-between" style="font-size:0.75rem;">
                            <span style="color:var(--ss-text-3);"><?= htmlspecialchars($cfgLabel) ?>:</span>
                            <span style="font-weight:600;color:var(--ss-text-2);font-family:var(--ss-font-mono);"><?= htmlspecialchars($cfgVal) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ==================== SUBSCRIPTION PLANS ==================== -->
<div class="ss-card mb-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-tags text-primary"></i> Subscription Plans</h3>
        <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-pen"></i> Edit Prices</a>
    </div>
    <div class="ss-card-body">
        <div class="row g-3">
            <?php foreach ($plans as $key => $plan): ?>
            <div class="col-md-3 col-6">
                <div class="ss-card-hover text-center" style="border:1px solid var(--ss-border);border-radius:var(--ss-r-lg);padding:1.5rem;height:100%;<?= $key === 'premium' ? 'border-color:var(--ss-primary);box-shadow:var(--ss-shadow-glow);' : '' ?>">
                    <?php if ($key === 'premium'): ?>
                    <span class="ss-badge ss-badge-primary mb-2"><i class="fas fa-star"></i> Popular</span>
                    <?php endif; ?>
                    <div style="font-size:0.82rem;font-weight:600;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;"><?= htmlspecialchars($plan['name']) ?></div>
                    <div style="font-size:1.75rem;font-weight:900;color:var(--ss-text);margin:0.5rem 0;"><?= number_format($plan['price']) ?><span style="font-size:0.78rem;color:var(--ss-text-3);"> RWF</span></div>
                    <?php if (!empty($plan['max_apps'])): ?>
                    <div style="font-size:0.75rem;color:var(--ss-text-3);">Max <?= (int)$plan['max_apps'] ?> applications</div>
                    <?php else: ?>
                    <div style="font-size:0.75rem;color:var(--ss-text-3);">Unlimited applications</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ==================== PAYMENT SETTINGS QUICK EDIT ==================== -->
<div class="ss-card mb-4">
    <div class="ss-card-header">
        <h3><i class="fas fa-cog text-primary"></i> Quick Payment Settings</h3>
    </div>
    <div class="ss-card-body">
        <form method="POST" action="<?= URL::to('admin/settings/update') ?>">
            <?= $csrf ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Currency</label>
                        <input type="text" class="ss-input" name="setting_default_currency" value="<?= htmlspecialchars($settings['default_currency'] ?? 'RWF') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Payment Provider</label>
                        <select class="ss-select" name="setting_payment_provider">
                            <option value="" <?= ($settings['payment_provider'] ?? '') === '' ? 'selected' : '' ?>>None</option>
                            <option value="stripe" <?= ($settings['payment_provider'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                            <option value="paypal" <?= ($settings['payment_provider'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                            <option value="both" <?= ($settings['payment_provider'] ?? '') === 'both' ? 'selected' : '' ?>>Both</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Subscription Price</label>
                        <input type="number" class="ss-input" name="setting_subscription_price" value="<?= htmlspecialchars($settings['subscription_price'] ?? '20000') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Free: Max Apps</label>
                        <input type="number" class="ss-input" name="setting_free_plan_max_applications" value="<?= htmlspecialchars($settings['free_plan_max_applications'] ?? '10') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Basic Plan (RWF)</label>
                        <input type="number" class="ss-input" name="setting_basic_plan_price" value="<?= htmlspecialchars($settings['basic_plan_price'] ?? '20000') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Premium Plan (RWF)</label>
                        <input type="number" class="ss-input" name="setting_premium_plan_price" value="<?= htmlspecialchars($settings['premium_plan_price'] ?? '50000') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ss-form-group">
                        <label class="ss-form-label">Enterprise (RWF)</label>
                        <input type="number" class="ss-input" name="setting_enterprise_plan_price" value="<?= htmlspecialchars($settings['enterprise_plan_price'] ?? '150000') ?>">
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Payment Settings</button>
                <a href="<?= URL::to('admin/settings') ?>" class="ss-btn ss-btn-light"><i class="fas fa-cog"></i> Full Settings</a>
            </div>
        </form>
    </div>
</div>

<!-- ==================== TRANSACTIONS TABLE ==================== -->
<div class="ss-card">
    <div class="ss-card-header">
        <h3><i class="fas fa-list text-primary"></i> All Transactions</h3>
        <span class="ss-badge ss-badge-primary"><?= $total ?> total</span>
    </div>
    <div class="ss-card-body" style="padding:0;">
        <?php if (!empty($payments)): ?>
        <div class="table-responsive">
            <table class="ss-table" data-table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rowNum = 1; foreach ($payments as $p):
                        $name = htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                        $sc = ['completed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'refunded' => 'info'][$p['status'] ?? 'pending'] ?? 'soft';
                    ?>
                    <tr>
                        <td style="font-weight:700;color:var(--ss-text-3);"><?= $rowNum++ ?></td>
                        <td>
                            <div class="table-avatar">
                                <?= Component::avatar($name, null, 'sm') ?>
                                <div>
                                    <div style="font-size:0.82rem;font-weight:600;"><?= $name ?></div>
                                    <div style="font-size:0.72rem;color:var(--ss-text-3);"><?= htmlspecialchars($p['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:700;"><?= number_format($p['amount'] ?? 0, 0) ?> <?= htmlspecialchars($p['currency'] ?? 'RWF') ?></td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($p['method'] ?? '—') ?></td>
                        <td style="font-size:0.75rem;font-family:var(--ss-font-mono);"><?= htmlspecialchars($p['transaction_id'] ?? '—') ?></td>
                        <td><?= Component::badge(ucfirst($p['status'] ?? 'pending'), $sc) ?></td>
                        <td style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars(date('M j, Y g:i a', strtotime($p['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <?php if (($p['status'] ?? '') === 'completed'): ?>
                            <form method="POST" action="<?= URL::to('admin/payments/' . (int)$p['id'] . '/refund') ?>" style="display:inline;" onsubmit="return confirm('Refund this payment?');">
                                <?= $csrf ?>
                                <button type="submit" class="ss-btn ss-btn-ghost ss-btn-xs" style="color:var(--ss-warning);"><i class="fas fa-undo"></i> Refund</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div style="padding:2rem;"><?= Component::emptyState(['icon' => 'fa-credit-card', 'title' => 'No payments yet', 'desc' => 'No payments have been recorded yet.']) ?></div>
        <?php endif; ?>
    </div>
</div>
