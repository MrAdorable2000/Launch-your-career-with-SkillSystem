<?php
use App\Helpers\URL;
use App\Helpers\CSRF;
?>
<h2>Reset your password 🔑</h2>
<p class="subtitle">Enter your new password below.</p>
<form method="POST" action="<?= URL::to('auth/reset-password') ?>" data-validate>
    <?= $csrfField ?? '' ?>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <div class="ss-form-group ss-float mb-3">
        <input type="password" name="password" id="resetPassword" placeholder=" " required data-min="8" data-password-strength="#pw-target">
        <label for="resetPassword">New password</label>
    </div>
    <div class="ss-form-group ss-float mb-3">
        <input type="password" name="password_confirmation" id="resetConfirm" placeholder=" " required data-match="password">
        <label for="resetConfirm">Confirm new password</label>
    </div>
    <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block ss-btn-lg"><i class="fas fa-key"></i> Reset Password</button>
</form>
<div class="text-center mt-4" style="font-size:0.875rem;">
    Remember your password? <a href="<?= URL::to('login') ?>" class="fw-bold">Sign in</a>
</div>
