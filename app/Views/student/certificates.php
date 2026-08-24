<?php
/**
 * Certificates + QR Verification (v3)
 *
 * Data passed from InnovationController::certificates():
 *   $student, $certificates (array with title, issuing_organization, certificate_number,
 *                            issued_date, verification_code, verified)
 *
 * Form posts to student/certificates/add with fields:
 *   title, issuing_organization, certificate_number, issued_date
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'My Certificates';

$certificates = $certificates ?? [];
$verified     = array_filter($certificates, fn($c) => !empty($c['verified']));
$pending      = array_filter($certificates, fn($c) => empty($c['verified']));
?>
<?= Component::pageHeader(
    'My Certificates',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Certificates</span>',
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addCertModal"><i class="fas fa-plus"></i> Add Certificate</button>'
) ?>

<!-- Stats -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-certificate',   'label' => 'Total',     'count' => count($certificates), 'color' => 'accent']) ?>
    <?= Component::statCard(['icon' => 'fa-check-circle',  'label' => 'Verified',  'count' => count($verified),     'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-clock',         'label' => 'Pending',   'count' => count($pending),      'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-qrcode',        'label' => 'QR Issued', 'count' => count($verified),     'color' => 'info']) ?>
</div>

<div class="ss-dashboard-grid">
    <div>
        <!-- Certificates list -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-certificate text-primary"></i> Your Certificates</h3>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($certificates)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($certificates as $c): ?>
                            <div class="ss-card ss-hover-lift" style="padding:1.25rem;border-left:4px solid <?= !empty($c['verified']) ? 'var(--ss-success)' : 'var(--ss-warning)' ?>;">
                                <div class="d-flex gap-3 align-items-start flex-wrap">
                                    <div style="width:56px;height:56px;border-radius:var(--ss-radius);background:var(--ss-gradient-warm);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div style="flex:1;min-width:200px;">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h5 style="font-size:0.95rem;margin:0;"><?= htmlspecialchars($c['title'] ?? '') ?></h5>
                                            <?php if (!empty($c['verified'])): ?>
                                                <span class="ss-verification-badge"><i class="fas fa-check"></i> Verified</span>
                                            <?php else: ?>
                                                <?= Component::badge('Pending', 'warning', 'fa-clock') ?>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size:0.82rem;color:var(--ss-text-3);margin-top:2px;"><?= htmlspecialchars($c['issuing_organization'] ?? '') ?></div>
                                        <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:0.78rem;color:var(--ss-text-2);">
                                            <span><i class="fas fa-calendar"></i> <?= htmlspecialchars($c['issued_date'] ?? 'N/A') ?></span>
                                            <span><i class="fas fa-hashtag"></i> <?= htmlspecialchars($c['certificate_number'] ?? 'N/A') ?></span>
                                        </div>
                                        <?php if (!empty($c['verification_code'])): ?>
                                            <div class="mt-2 p-2" style="background:var(--ss-surface-2);border-radius:var(--ss-radius-sm);font-family:var(--ss-font-mono);font-size:0.78rem;">
                                                <i class="fas fa-shield-alt text-success"></i>
                                                <strong>Verification Code:</strong> <?= htmlspecialchars($c['verification_code']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <?php if (!empty($c['verified']) && !empty($c['verification_code'])): ?>
                                            <a href="<?= URL::to('verify/' . $c['verification_code']) ?>" target="_blank" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-qrcode"></i> View QR</a>
                                            <button class="ss-btn ss-btn-light ss-btn-sm" onclick="navigator.clipboard.writeText('<?= URL::to('verify/' . $c['verification_code']) ?>').then(()=>window.ssToast&&ssToast.show('Link copied!','success'))">
                                                <i class="fas fa-copy"></i> Copy Link
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState([
                        'icon'   => 'fa-certificate',
                        'title'  => 'No certificates yet',
                        'desc'   => 'Add your industry certifications to showcase your skills and earn badges.',
                        'action' => '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addCertModal"><i class="fas fa-plus"></i> Add Certificate</button>'
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <!-- About verification -->
        <div class="ss-card mb-4 ss-card-gradient ss-animate-fade-up">
            <div class="ss-card-body">
                <div style="font-size:0.85rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;"><i class="fas fa-qrcode"></i> QR Verification</div>
                <h4 style="color:#fff;margin:0.5rem 0;font-size:1.1rem;">Tamper-proof credentials</h4>
                <p style="color:rgba(255,255,255,0.85);font-size:0.85rem;line-height:1.5;">
                    Every certificate you add gets a unique verification code and shareable QR link. Employers can verify authenticity instantly — no fake credentials, no manual checks.
                </p>
                <a href="<?= URL::to('verify') ?>" target="_blank" class="ss-btn ss-btn-light ss-btn-block" style="background:rgba(255,255,255,0.2);color:#fff;border:none;">
                    <i class="fas fa-external-link-alt"></i> Open Verifier Page
                </a>
            </div>
        </div>

        <!-- Tips -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-info-circle text-primary"></i> Tips</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2" style="font-size:0.82rem;color:var(--ss-text-2);">
                    <div><i class="fas fa-check text-success me-2"></i> Add certificates from recognized issuers (Coursera, AWS, Google, etc.)</div>
                    <div><i class="fas fa-check text-success me-2"></i> Unverified certificates become verified after admin review</div>
                    <div><i class="fas fa-check text-success me-2"></i> Share your verification link on LinkedIn &amp; resumes</div>
                    <div><i class="fas fa-check text-success me-2"></i> Verified certificates count toward your leaderboard score</div>
                    <div><i class="fas fa-check text-success me-2"></i> Earn badges by adding multiple verified certificates</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Certificate Modal -->
<div class="modal fade" id="addCertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('student/certificates/add') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-certificate text-primary me-2"></i> Add Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <?= Component::floatField('title', 'Certificate Title *', 'text', null, ['required' => true, 'id' => 'certTitle']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= Component::floatField('issuing_organization', 'Issuing Organization *', 'text', null, ['required' => true, 'id' => 'certOrg']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= Component::floatField('certificate_number', 'Certificate Number (optional)', 'text', null, ['id' => 'certNum']) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Issue Date</label>
                                <input type="date" name="issued_date" class="ss-input" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
                            </div>
                        </div>
                    </div>
                    <?= Component::alert(
                        'A unique verification code & QR link will be generated automatically. The certificate will appear as "Pending" until verified by an admin.',
                        'info'
                    ) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-plus"></i> Add Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>
