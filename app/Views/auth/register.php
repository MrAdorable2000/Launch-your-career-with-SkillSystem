<?php
/**
 * Register page
 */
use App\Helpers\URL;
$old = $_POST ?? [];
?>
<h2>Create your account ✨</h2>
<p class="subtitle">Join SkillSystem and start building your career today. It's free for students.</p>

<form method="POST" action="<?= URL::to('auth/register') ?>" data-validate>
    <?= $csrfField ?? '' ?>

    <div class="ss-form-group mb-3">
        <label class="ss-form-label">I am a <span class="req">*</span></label>
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <label style="display:block;cursor:pointer;">
                    <input type="radio" name="role" value="student" <?= (($old['role'] ?? 'student') === 'student') ? 'checked' : '' ?> hidden required>
                    <div class="ss-card" style="padding:1rem;text-align:center;transition:all 0.15s;" onclick="this.parentElement.style.borderColor='var(--ss-primary)';">
                        <div style="font-size:1.5rem;color:var(--ss-primary);margin-bottom:4px;"><i class="fas fa-user-graduate"></i></div>
                        <div style="font-weight:600;font-size:0.82rem;">Student</div>
                    </div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label style="display:block;cursor:pointer;">
                    <input type="radio" name="role" value="employer" <?= ($old['role'] ?? '') === 'employer' ? 'checked' : '' ?> hidden>
                    <div class="ss-card" style="padding:1rem;text-align:center;transition:all 0.15s;">
                        <div style="font-size:1.5rem;color:var(--ss-success);margin-bottom:4px;"><i class="fas fa-building"></i></div>
                        <div style="font-weight:600;font-size:0.82rem;">Employer</div>
                    </div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label style="display:block;cursor:pointer;">
                    <input type="radio" name="role" value="university" <?= ($old['role'] ?? '') === 'university' ? 'checked' : '' ?> hidden>
                    <div class="ss-card" style="padding:1rem;text-align:center;transition:all 0.15s;">
                        <div style="font-size:1.5rem;color:var(--ss-warning);margin-bottom:4px;"><i class="fas fa-university"></i></div>
                        <div style="font-weight:600;font-size:0.82rem;">University</div>
                    </div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label style="display:block;cursor:pointer;">
                    <input type="radio" name="role" value="mentor" <?= ($old['role'] ?? '') === 'mentor' ? 'checked' : '' ?> hidden>
                    <div class="ss-card" style="padding:1rem;text-align:center;transition:all 0.15s;">
                        <div style="font-size:1.5rem;color:var(--ss-info);margin-bottom:4px;"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div style="font-weight:600;font-size:0.82rem;">Mentor</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-6">
            <div class="ss-form-group ss-float">
                <input type="text" name="first_name" id="regFirst" placeholder=" " required value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                <label for="regFirst">First name</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ss-form-group ss-float">
                <input type="text" name="last_name" id="regLast" placeholder=" " required value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                <label for="regLast">Last name</label>
            </div>
        </div>
    </div>
    <div class="ss-form-group ss-float mb-3">
        <input type="email" name="email" id="regEmail" placeholder=" " required data-validate="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        <label for="regEmail">Email address</label>
    </div>
    <div class="ss-form-group mb-3">
        <label class="ss-form-label" for="regPassword">Password <span class="req">*</span></label>
        <div class="ss-pw-wrap">
            <input type="password" name="password" id="regPassword" placeholder="Enter at least 8 characters" required data-min="8" data-password-strength="#pw-strength-target" class="ss-input">
            <button type="button" class="ss-pw-toggle" onclick="ssTogglePw(this)" aria-label="Show password"><i class="fas fa-eye"></i></button>
        </div>
    </div>
    <div class="ss-form-group mb-3">
        <label class="ss-form-label" for="regConfirm">Confirm password <span class="req">*</span></label>
        <div class="ss-pw-wrap">
            <input type="password" name="password_confirmation" id="regConfirm" placeholder="Re-enter your password" required data-match="password" class="ss-input">
            <button type="button" class="ss-pw-toggle" onclick="ssTogglePw(this)" aria-label="Show password"><i class="fas fa-eye"></i></button>
        </div>
    </div>
    <div class="ss-form-group mb-3">
        <label class="ss-check">
            <input type="checkbox" name="terms" required>
            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
        </label>
    </div>
    <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block ss-btn-lg"><i class="fas fa-user-plus"></i> Create account</button>
</form>

<div class="text-center mt-4" style="font-size:0.875rem;">
    Already have an account? <a href="<?= URL::to('login') ?>" class="fw-bold">Sign in</a>
</div>

<style>
input[type="radio"]:checked + .ss-card { border-color: var(--ss-primary) !important; box-shadow: var(--ss-ring) !important; background: var(--ss-primary-light) !important; }
</style>
