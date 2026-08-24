<?php
/**
 * Forgot password page
 */
use App\Helpers\URL;
?>
<h2>Reset your password 🔐</h2>
<p class="subtitle">Enter your email address and we'll send you a link to reset your password.</p>

<form method="POST" action="<?= URL::to('auth/forgot-password') ?>" data-validate>
    <?= $csrfField ?? '' ?>
    <div class="ss-form-group ss-float mb-3">
        <input type="email" name="email" id="forgotEmail" placeholder=" " required data-validate="email" autofocus>
        <label for="forgotEmail">Email address</label>
    </div>
    <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block ss-btn-lg"><i class="fas fa-paper-plane"></i> Send reset link</button>
</form>

<div class="ss-alert ss-alert-info mt-4" style="font-size:0.82rem;">
    <i class="fas fa-info-circle alert-icon"></i>
    <div class="alert-body"><strong>Check your inbox</strong> — if an account exists with that email, you'll receive a reset link within 5 minutes. Don't forget to check your spam folder.</div>
</div>

<div class="text-center mt-4" style="font-size:0.875rem;">
    Remember your password? <a href="<?= URL::to('login') ?>" class="fw-bold">Back to sign in</a>
</div>
