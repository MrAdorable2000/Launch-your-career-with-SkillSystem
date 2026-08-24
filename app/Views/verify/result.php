<?php
/**
 * Verification Result — Shows certificate details or "not found"
 *
 * Data passed from InnovationController::verifyByCode():
 *   $code         — string (the code entered by the user)
 *   $certificate  — array with title, issuing_organization, certificate_number,
 *                   issued_date, first_name, last_name, email, department,
 *                   verification_code — or null if not found
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;

$pageTitle = 'Verification Result';

$cert = $certificate ?? null;
?>
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2.5rem 1rem;">
    <div style="max-width:720px;width:100%;">

        <!-- Top: verify-another link -->
        <div class="text-center mb-3">
            <a href="<?= URL::to('verify') ?>" style="font-size:0.85rem;color:var(--ss-text-2);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Verify another certificate
            </a>
        </div>

        <?php if (!empty($cert)): ?>
            <!-- VALID CERTIFICATE -->
            <div class="ss-card ss-animate-fade-up" style="overflow:hidden;box-shadow:var(--ss-shadow-md);">
                <!-- Success banner -->
                <div style="background:var(--ss-grad-success);padding:2.25rem 1.5rem 1.75rem;text-align:center;color:#fff;position:relative;">
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;background-image:radial-gradient(circle at 20% 50%, rgba(255,255,255,0.12) 0%, transparent 50%),radial-gradient(circle at 80% 30%, rgba(255,255,255,0.08) 0%, transparent 50%);pointer-events:none;"></div>
                    <div style="position:relative;">
                        <div style="width:88px;height:88px;border-radius:50%;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.4);display:inline-flex;align-items:center;justify-content:center;font-size:2.5rem;margin-bottom:0.85rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 style="color:#fff;font-size:1.65rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Certificate Verified</h2>
                        <p style="color:rgba(255,255,255,0.92);font-size:0.95rem;margin:0.5rem 0 0;">
                            This certificate is authentic and was issued through SkillSystem.
                        </p>
                    </div>
                </div>

                <!-- Certificate body -->
                <div class="ss-card-body" style="padding:2rem;">
                    <!-- Certificate title block -->
                    <div class="text-center" style="margin-bottom:1.75rem;">
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--ss-grad-warm);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.75rem;margin-bottom:0.85rem;box-shadow:var(--ss-shadow-sm);">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3 style="font-size:1.3rem;margin:0;font-weight:800;"><?= htmlspecialchars($cert['title'] ?? 'Certificate') ?></h3>
                        <div style="font-size:0.875rem;color:var(--ss-text-3);margin-top:0.25rem;">
                            Issued by <strong style="color:var(--ss-text-2);"><?= htmlspecialchars($cert['issuing_organization'] ?? 'Unknown Organization') ?></strong>
                        </div>
                    </div>

                    <!-- Detail rows -->
                    <div class="row g-3" style="border-top:1px solid var(--ss-border);border-bottom:1px solid var(--ss-border);padding:1.5rem 0;margin-bottom:1.75rem;">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2" style="margin-bottom:0.3rem;">
                                <i class="fas fa-user text-primary" style="font-size:0.8rem;"></i>
                                <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--ss-text-3);font-weight:700;">Recipient</span>
                            </div>
                            <div style="font-weight:700;font-size:1rem;color:var(--ss-text);"><?= htmlspecialchars(trim(($cert['first_name'] ?? '') . ' ' . ($cert['last_name'] ?? '')) ?: '—') ?></div>
                            <?php if (!empty($cert['email'])): ?>
                                <div style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars($cert['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2" style="margin-bottom:0.3rem;">
                                <i class="fas fa-graduation-cap text-info" style="font-size:0.8rem;"></i>
                                <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--ss-text-3);font-weight:700;">Department</span>
                            </div>
                            <div style="font-weight:600;font-size:1rem;color:var(--ss-text);"><?= htmlspecialchars($cert['department'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2" style="margin-bottom:0.3rem;">
                                <i class="fas fa-calendar-alt text-success" style="font-size:0.8rem;"></i>
                                <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--ss-text-3);font-weight:700;">Issue Date</span>
                            </div>
                            <div style="font-weight:600;font-size:1rem;color:var(--ss-text);">
                                <?= !empty($cert['issued_date']) ? htmlspecialchars(date('F j, Y', strtotime($cert['issued_date']))) : '—' ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2" style="margin-bottom:0.3rem;">
                                <i class="fas fa-hashtag text-warning" style="font-size:0.8rem;"></i>
                                <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--ss-text-3);font-weight:700;">Certificate Number</span>
                            </div>
                            <div style="font-weight:600;font-size:0.95rem;font-family:'Courier New',monospace;color:var(--ss-text);"><?= htmlspecialchars($cert['certificate_number'] ?? '—') ?></div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2" style="margin-bottom:0.3rem;">
                                <i class="fas fa-key text-accent" style="font-size:0.8rem;"></i>
                                <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--ss-text-3);font-weight:700;">Verification Code</span>
                            </div>
                            <div style="font-weight:700;font-size:1rem;font-family:'Courier New',monospace;color:var(--ss-primary);background:var(--ss-primary-light);padding:0.5rem 0.875rem;border-radius:var(--ss-r-sm);display:inline-block;">
                                <?= htmlspecialchars($cert['verification_code'] ?? '') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Authentic record badge -->
                    <div class="ss-alert ss-alert-success" style="margin-bottom:1.25rem;">
                        <i class="fas fa-shield-halved alert-icon"></i>
                        <div class="alert-body">
                            <div class="alert-title">Authentic Record</div>
                            This certificate was verified on <?= htmlspecialchars(date('F j, Y \a\t g:i A')) ?>. The recipient holds a verified credential from <?= htmlspecialchars($cert['issuing_organization'] ?? 'the issuing organization') ?>.
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="<?= URL::to('verify') ?>" class="ss-btn ss-btn-soft">
                            <i class="fas fa-redo"></i> Verify Another
                        </a>
                        <button type="button" class="ss-btn ss-btn-light" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Certificate
                        </button>
                        <a href="<?= URL::to('/') ?>" class="ss-btn ss-btn-ghost">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- NOT FOUND -->
            <div class="ss-card ss-animate-fade-up text-center" style="padding:3rem 2rem;box-shadow:var(--ss-shadow-md);">
                <div style="width:108px;height:108px;border-radius:50%;background:var(--ss-danger-light);color:var(--ss-danger);display:inline-flex;align-items:center;justify-content:center;font-size:2.75rem;margin-bottom:1.5rem;position:relative;">
                    <i class="fas fa-times-circle"></i>
                    <span style="position:absolute;top:-4px;right:-4px;width:28px;height:28px;border-radius:50%;background:var(--ss-danger);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;border:3px solid var(--ss-surface);">
                        <i class="fas fa-exclamation"></i>
                    </span>
                </div>
                <div class="ss-badge ss-badge-danger" style="margin-bottom:1rem;">
                    <i class="fas fa-ban"></i> Verification Failed
                </div>
                <h2 style="font-size:1.65rem;font-weight:800;color:var(--ss-text);letter-spacing:-0.02em;">Certificate Not Found</h2>
                <p style="color:var(--ss-text-2);font-size:0.95rem;margin:0.75rem auto 1.5rem;max-width:440px;line-height:1.6;">
                    The verification code
                    <code style="background:var(--ss-surface-2);padding:3px 10px;border-radius:6px;font-family:'Courier New',monospace;font-weight:700;color:var(--ss-text);border:1px solid var(--ss-border);"><?= htmlspecialchars($code ?? '') ?></code>
                    does not match any certificate in our system. Please double-check the code and try again.
                </p>

                <!-- Helpful tips -->
                <div class="ss-card" style="background:var(--ss-surface-2);border-color:var(--ss-border);padding:1.25rem 1.5rem;text-align:left;margin-bottom:1.75rem;">
                    <div style="font-size:0.82rem;font-weight:700;color:var(--ss-text-2);margin-bottom:0.75rem;"><i class="fas fa-lightbulb text-warning"></i> Common reasons:</div>
                    <ul style="font-size:0.82rem;color:var(--ss-text-2);margin:0;padding-left:1.25rem;line-height:1.7;">
                        <li>The code was typed incorrectly — codes are case-insensitive but every character matters.</li>
                        <li>The certificate was revoked by the issuing organization.</li>
                        <li>The certificate is still pending issuance and not yet verifiable.</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?= URL::to('verify') ?>" class="ss-btn ss-btn-gradient">
                        <i class="fas fa-redo"></i> Try Again
                    </a>
                    <a href="<?= URL::to('/') ?>" class="ss-btn ss-btn-light">
                        <i class="fas fa-home"></i> Go Home
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
