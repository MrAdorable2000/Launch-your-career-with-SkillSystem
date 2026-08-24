<?php
/**
 * Login page
 */
use App\Helpers\URL;
?>
<h2>Welcome back 👋</h2>
<p class="subtitle">Sign in to your SkillSystem account to continue your career journey.</p>

<form method="POST" action="<?= URL::to('auth/login') ?>" data-validate>
    <?= $csrfField ?? '' ?>
    <div class="ss-form-group ss-float mb-3">
        <input type="email" name="email" id="loginEmail" placeholder=" " required data-validate="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus>
        <label for="loginEmail">Email address</label>
    </div>
    <div class="ss-form-group mb-3">
        <label class="ss-form-label" for="loginPassword">Password</label>
        <div class="ss-pw-wrap">
            <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required data-min="1" class="ss-input" autofocus>
            <button type="button" class="ss-pw-toggle" onclick="ssTogglePw(this)" aria-label="Show password"><i class="fas fa-eye"></i></button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="ss-check"><input type="checkbox" name="remember"> <span>Remember me</span></label>
        <a href="<?= URL::to('forgot-password') ?>" style="font-size:0.85rem;font-weight:600;">Forgot password?</a>
    </div>
    <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block ss-btn-lg"><i class="fas fa-sign-in-alt"></i> Sign in</button>
</form>

<div class="text-center my-3" style="position:relative;">
    <hr style="border-color:var(--ss-border);">
    <span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--ss-bg);padding:0 1rem;color:var(--ss-text-3);font-size:0.8rem;">OR CONTINUE WITH</span>
</div>

<div class="row g-2">
    <div class="col-6"><a href="#" class="ss-btn ss-btn-light ss-btn-block"><i class="fab fa-google me-2" style="color:#EA4335;"></i> Google</a></div>
    <div class="col-6"><a href="#" class="ss-btn ss-btn-light ss-btn-block"><i class="fab fa-linkedin me-2" style="color:#0A66C2;"></i> LinkedIn</a></div>
</div>

<div class="text-center mt-4" style="font-size:0.875rem;">
    Don't have an account? <a href="<?= URL::to('register') ?>" class="fw-bold">Sign up for free</a>
</div>

<?php // Demo credentials block removed for production security (was leaking admin/student/employer emails and password). ?>
