<?php
/**
 * Employer — Post a Job (premium redesign v3)
 *
 * Data passed from EmployerController::postJob(): none.
 *
 * Submits to EmployerController::storeJob() via POST /employer/jobs/store
 * Controller reads: title, description, requirements, responsibilities,
 *                   salary_min (getInt), salary_max (getInt),
 *                   location, type (in:full-time,part-time,contract,freelance),
 *                   remote (checkbox - presence checked), deadline
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Post a Job';

$jobTypes = [
    'full-time' => 'Full-time',
    'part-time' => 'Part-time',
    'contract'  => 'Contract',
    'freelance' => 'Freelance',
];
?>
<?= Component::pageHeader(
    'Post a New Job',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <a href="' . URL::to('employer/jobs') . '">My Jobs</a> / <span>Post a Job</span>',
    '<a href="' . URL::to('employer/jobs') . '" class="ss-btn ss-btn-light"><i class="fas fa-arrow-left"></i> <span class="d-none d-md-inline">Back to Jobs</span></a>'
) ?>

<form method="POST" action="<?= URL::to('employer/jobs/store') ?>" data-validate>
    <?= $csrfField ?? '' ?>
    <div class="row g-4">
        <!-- ============== LEFT — main content ============== -->
        <div class="col-lg-8">
            <!-- Job details -->
            <div class="ss-card mb-4 ss-animate-fade-up">
                <div class="ss-card-header">
                    <h3><i class="fas fa-file-alt text-primary"></i> Job Details</h3>
                </div>
                <div class="ss-card-body">
                    <div class="ss-form-group ss-float">
                        <input type="text" name="title" id="title" placeholder=" " required minlength="3">
                        <label for="title">Job Title <span class="req">*</span></label>
                    </div>
                    <div class="ss-form-hint mt-n2 mb-3" style="font-size:0.75rem;color:var(--ss-text-3);">
                        e.g. Senior Backend Engineer, Product Designer, Sales Associate
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="description">Job Description <span class="req">*</span></label>
                        <textarea name="description" id="description" class="ss-textarea" required minlength="20" placeholder="Describe the role, day-to-day responsibilities, and what success looks like in this position..."></textarea>
                        <div class="ss-form-hint">Minimum 20 characters. Mention team structure and reporting line.</div>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="requirements">Requirements</label>
                        <textarea name="requirements" id="requirements" class="ss-textarea" placeholder="List the skills, experience, education and certifications needed for this role. One per line.&#10;&#10;e.g.&#10;3+ years of PHP experience&#10;Bachelor's degree in Computer Science&#10;Strong communication skills"></textarea>
                        <div class="ss-form-hint">One requirement per line — they'll be displayed as a checklist.</div>
                    </div>

                    <div class="ss-form-group mb-0">
                        <label class="ss-form-label" for="responsibilities">Responsibilities</label>
                        <textarea name="responsibilities" id="responsibilities" class="ss-textarea" placeholder="What will the candidate do day-to-day? One per line.&#10;&#10;e.g.&#10;Build and maintain REST APIs&#10;Collaborate with the product team&#10;Mentor junior developers"></textarea>
                        <div class="ss-form-hint">One responsibility per line.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============== RIGHT — sidebar (sticky) ============== -->
        <div class="col-lg-4">
            <div style="position:sticky;top:90px;">
                <!-- Publish card -->
                <div class="ss-card mb-4 ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-rocket text-primary"></i> Publish</h3>
                    </div>
                    <div class="ss-card-body">
                        <div class="d-flex flex-column gap-2">
                            <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block"><i class="fas fa-paper-plane"></i> Publish Job</button>
                            <a href="<?= URL::to('employer/jobs') ?>" class="ss-btn ss-btn-light ss-btn-block"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                        <div class="divider-h my-3"></div>
                        <div style="font-size:0.78rem;color:var(--ss-text-3);line-height:1.6;">
                            <i class="fas fa-info-circle text-primary"></i>
                            Your job will be visible to candidates immediately after publishing. You can edit or pause it anytime from <a href="<?= URL::to('employer/jobs') ?>" class="fw-semibold">My Jobs</a>.
                        </div>
                    </div>
                </div>

                <!-- Job settings -->
                <div class="ss-card ss-animate-fade-up">
                    <div class="ss-card-header">
                        <h3><i class="fas fa-cog text-primary"></i> Job Settings</h3>
                    </div>
                    <div class="ss-card-body">
                        <!-- Job Type -->
                        <div class="ss-form-group ss-float">
                            <select name="type" id="type" required>
                                <?php foreach ($jobTypes as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="type">Job Type <span class="req">*</span></label>
                        </div>

                        <!-- Location -->
                        <div class="ss-form-group ss-float">
                            <input type="text" name="location" id="location" placeholder=" " required>
                            <label for="location">Location <span class="req">*</span></label>
                        </div>

                        <!-- Salary range -->
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="ss-form-group ss-float">
                                    <input type="number" name="salary_min" id="salary_min" min="0" step="1000" placeholder=" ">
                                    <label for="salary_min">Salary Min (RWF)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="ss-form-group ss-float">
                                    <input type="number" name="salary_max" id="salary_max" min="0" step="1000" placeholder=" ">
                                    <label for="salary_max">Salary Max (RWF)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div class="ss-form-group ss-float">
                            <input type="date" name="deadline" id="deadline" placeholder=" " required min="<?= date('Y-m-d') ?>">
                            <label for="deadline">Application Deadline <span class="req">*</span></label>
                        </div>

                        <!-- Remote toggle -->
                        <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background:var(--ss-surface-2);border:1px solid var(--ss-border);">
                            <div>
                                <div style="font-size:0.85rem;font-weight:600;">
                                    <i class="fas fa-wifi text-info me-1"></i> Remote-friendly
                                </div>
                                <div style="font-size:0.72rem;color:var(--ss-text-3);">Allow candidates to work remotely</div>
                            </div>
                            <label class="ss-switch">
                                <input type="checkbox" name="remote" value="1">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
