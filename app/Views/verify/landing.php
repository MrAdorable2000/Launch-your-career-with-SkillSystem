<?php
/**
 * Verification Landing — Public page for verifying certificates
 *
 * Route: GET /verify  →  InnovationController@verifyLanding
 * Result: GET /verify/{code}  →  InnovationController@verifyByCode
 *
 * The form below submits to /verify/<code> via a tiny JS redirect, since the
 * verify route is path-based, not query-string based.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;

$pageTitle = 'Verify a Certificate';
?>
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:3rem 1rem;">
    <div style="max-width:680px;width:100%;text-align:center;">

        <!-- Animated shield icon -->
        <div style="width:108px;height:108px;border-radius:50%;background:var(--ss-grad-primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:2.75rem;margin-bottom:1.75rem;box-shadow:var(--ss-shadow-primary);position:relative;" class="ss-animate-float">
            <i class="fas fa-shield-alt"></i>
            <span style="position:absolute;bottom:-6px;right:-6px;width:36px;height:36px;border-radius:50%;background:var(--ss-success);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border:3px solid var(--ss-surface);">
                <i class="fas fa-check"></i>
            </span>
        </div>

        <div class="ss-badge ss-badge-soft" style="margin-bottom:1rem;">
            <i class="fas fa-lock"></i> Trusted · Authentic · Secure
        </div>

        <h1 style="font-size:2.5rem;font-weight:900;letter-spacing:-0.02em;margin-bottom:1rem;color:var(--ss-text);">Verify a Certificate</h1>
        <p style="font-size:1.05rem;color:var(--ss-text-2);margin:0 auto 2.5rem;max-width:540px;line-height:1.6;">
            Enter the verification code or scan the QR code on any SkillSystem certificate to instantly confirm its authenticity and view the recipient's details.
        </p>

        <!-- Verification form -->
        <div class="ss-card" style="padding:2rem;max-width:540px;margin:0 auto;text-align:left;box-shadow:var(--ss-shadow-md);">
            <form id="verifyForm" method="GET" action="<?= URL::to('verify') ?>">
                <?= $csrfField ?? '' ?>
                <div class="ss-form-group" style="margin-bottom:1.25rem;">
                    <label class="ss-form-label" for="verifyCode">Verification Code <span class="req">*</span></label>
                    <div class="ss-input-icon">
                        <i class="fas fa-key"></i>
                        <input type="text" name="code" id="verifyCode" class="ss-input" placeholder="SS-XXXXXXXXXXXX" required
                               style="text-align:center;font-family:'Courier New',monospace;font-size:1.15rem;letter-spacing:0.08em;text-transform:uppercase;font-weight:700;"
                               autocomplete="off" spellcheck="false">
                    </div>
                    <div class="ss-form-hint">Codes are case-insensitive and typically start with <code style="background:var(--ss-surface-2);padding:1px 6px;border-radius:4px;font-family:'Courier New',monospace;font-size:0.78rem;">SS-</code></div>
                </div>
                <button type="submit" class="ss-btn ss-btn-gradient ss-btn-lg ss-btn-block">
                    <i class="fas fa-search"></i> Verify Now
                </button>
            </form>
        </div>

        <!-- How verification works info card -->
        <div class="ss-card" style="margin-top:1.5rem;background:var(--ss-info-light);border-color:rgba(var(--ss-info-rgb),0.3);padding:1.5rem;text-align:left;">
            <div style="display:flex;gap:0.875rem;align-items:flex-start;">
                <div style="width:38px;height:38px;border-radius:var(--ss-r-sm);background:var(--ss-info);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                    <i class="fas fa-info"></i>
                </div>
                <div style="font-size:0.875rem;color:var(--ss-text-2);line-height:1.6;">
                    <strong style="color:var(--ss-text);">How verification works:</strong>
                    Every certificate issued through SkillSystem gets a unique verification code. Enter the code above to confirm the certificate is genuine, view the recipient's name, the issuing organization, and the issue date.
                </div>
            </div>
        </div>

        <!-- Trust badges -->
        <div class="d-flex flex-wrap justify-content-center gap-3" style="margin-top:2rem;">
            <div class="ss-chip"><i class="fas fa-shield-alt text-success"></i> Tamper-proof</div>
            <div class="ss-chip"><i class="fas fa-qrcode text-primary"></i> QR-enabled</div>
            <div class="ss-chip"><i class="fas fa-globe text-info"></i> Publicly verifiable</div>
            <div class="ss-chip"><i class="fas fa-bolt text-warning"></i> Instant results</div>
        </div>

        <!-- Back home link -->
        <div style="margin-top:2.5rem;">
            <a href="<?= URL::to('/') ?>" class="ss-btn ss-btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to home
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('verifyForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('verifyCode');
        const code = (input.value || '').trim();
        if (!code) {
            input.focus();
            return;
        }
        // Route is path-based: /verify/{code}
        const base = <?= json_encode(URL::to('verify')) ?>;
        const sep = base.endsWith('/') ? '' : '/';
        window.location.href = base + sep + encodeURIComponent(code);
    });
})();
</script>
