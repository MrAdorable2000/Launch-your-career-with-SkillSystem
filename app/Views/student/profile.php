<?php
/**
 * Student Profile — Premium redesign (v3)
 *
 * Data passed from StudentController::profile():
 *   $student, $skills, $education, $experience, $allSkills
 *
 * Form posts to student/profile/update with fields:
 *   bio, department, year_of_study, gpa, linkedin, github, website,
 *   skills_summary, first_name, last_name, phone,
 *   skills[] (array of skill IDs), proficiency_{skillId}, avatar ($_FILES)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'My Profile';

$student    = $student ?? [];
$skills     = $skills ?? [];
$education  = $education ?? [];
$experience = $experience ?? [];
$allSkills  = $allSkills ?? [];

$firstName   = $student['first_name'] ?? '';
$lastName    = $student['last_name'] ?? '';
$fullName    = trim($firstName . ' ' . $lastName);
$initials    = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
$userAvatar  = $userAvatar ?? Session::get('userAvatar');
$completion  = (int)($student['profile_completion'] ?? 0);
$chartColors = Theme::chartColors();

// Pre-compute which skills are checked + proficiency for fast lookup
$selectedSkills = [];
foreach ($skills as $s) {
    $selectedSkills[$s['id']] = $s['proficiency_level'] ?? 'intermediate';
}

$proficiencyPct = [
    'beginner'     => 25,
    'intermediate' => 55,
    'advanced'     => 80,
    'expert'       => 100,
];
?>
<?= Component::pageHeader(
    'My Profile',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>My Profile</span>',
    '<a href="' . URL::to('student/resume') . '" class="ss-btn ss-btn-light"><i class="fas fa-file-alt"></i> <span class="d-none d-md-inline">Resume</span></a>' .
    '<a href="#pane-edit" class="ss-btn ss-btn-gradient" data-tab="#pane-edit"><i class="fas fa-pen"></i> <span class="d-none d-md-inline">Edit Profile</span></a>'
) ?>

<!-- ==================== COVER + PROFILE HEADER ==================== -->
<div class="ss-profile-cover ss-animate-fade-up">
    <div class="cover-pattern"></div>
    <div class="position-absolute" style="bottom:1.25rem;right:1.5rem;">
        <label class="ss-btn ss-btn-light ss-btn-sm mb-0" style="background:rgba(255,255,255,0.18);color:#fff;border:none;cursor:pointer;">
            <i class="fas fa-camera"></i> Change Cover
            <input type="file" accept="image/*" style="display:none;" disabled>
        </label>
    </div>
</div>

<div class="ss-profile-header ss-animate-fade-up ss-delay-1">
    <div class="row g-4 align-items-center">
        <div class="col-lg-auto text-center text-lg-start">
            <div class="ss-avatar ss-avatar-2xl mx-auto" style="width:128px;height:128px;">
                <?php if (!empty($userAvatar)): ?>
                    <img src="<?= URL::asset($userAvatar) ?>" alt="<?= htmlspecialchars($fullName) ?>">
                <?php else: ?>
                    <?= $initials ?: 'U' ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="mb-0" style="font-size:1.6rem;font-weight:800;"><?= htmlspecialchars($fullName ?: 'Student') ?></h2>
                <?= Component::badge('Verified', 'success', 'fa-check-circle') ?>
            </div>
            <div class="d-flex flex-wrap gap-3 mb-2" style="font-size:0.88rem;color:var(--ss-text-secondary);">
                <?php if (!empty($student['department'])): ?>
                    <span><i class="fas fa-graduation-cap text-primary me-1"></i><?= htmlspecialchars($student['department']) ?></span>
                <?php endif; ?>
                <?php if (!empty($student['uni_name'])): ?>
                    <span><i class="fas fa-university text-primary me-1"></i><?= htmlspecialchars($student['uni_name']) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-envelope text-primary me-1"></i><?= htmlspecialchars($student['email'] ?? '') ?></span>
                <?php if (!empty($student['phone'])): ?>
                    <span><i class="fas fa-phone text-primary me-1"></i><?= htmlspecialchars($student['phone']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Social links -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php if (!empty($student['linkedin'])): ?>
                    <a href="<?= htmlspecialchars($student['linkedin']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-icon" title="LinkedIn"><i class="fab fa-linkedin-in" style="color:#0A66C2;"></i></a>
                <?php endif; ?>
                <?php if (!empty($student['github'])): ?>
                    <a href="<?= htmlspecialchars($student['github']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-icon" title="GitHub"><i class="fab fa-github"></i></a>
                <?php endif; ?>
                <?php if (!empty($student['website'])): ?>
                    <a href="<?= htmlspecialchars($student['website']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-icon" title="Website"><i class="fas fa-globe"></i></a>
                <?php endif; ?>
                <a href="mailto:<?= htmlspecialchars($student['email'] ?? '') ?>" class="ss-btn ss-btn-icon" title="Email"><i class="fas fa-envelope"></i></a>
            </div>

            <!-- Completion progress -->
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div style="flex:1;min-width:220px;max-width:380px;">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.78rem;">
                        <span class="fw-semibold">Profile completion</span>
                        <span class="text-primary fw-bold"><?= $completion ?>%</span>
                    </div>
                    <?= Component::progress($completion, 'primary', '') ?>
                </div>
                <div class="d-flex gap-2">
                    <?= Component::badge(count($skills) . ' skills', 'primary', 'fa-code') ?>
                    <?= Component::badge(count($experience) . ' experience', 'info', 'fa-briefcase') ?>
                    <?= Component::badge(count($education) . ' education', 'success', 'fa-university') ?>
                </div>
            </div>
        </div>
        <div class="col-lg-auto text-lg-end">
            <a href="#pane-edit" class="ss-btn ss-btn-gradient" data-tab="#pane-edit"><i class="fas fa-pen"></i> Edit Profile</a>
        </div>
    </div>
</div>

<!-- ==================== TABS ==================== -->
<div data-tabs class="ss-animate-fade-up ss-delay-2">
    <div class="ss-tabs">
        <button class="ss-tab active" data-tab="#pane-about"><i class="fas fa-user"></i> About</button>
        <button class="ss-tab" data-tab="#pane-skills"><i class="fas fa-code"></i> Skills <span class="count"><?= count($skills) ?></span></button>
        <button class="ss-tab" data-tab="#pane-education"><i class="fas fa-university"></i> Education <span class="count"><?= count($education) ?></span></button>
        <button class="ss-tab" data-tab="#pane-experience"><i class="fas fa-briefcase"></i> Experience <span class="count"><?= count($experience) ?></span></button>
        <button class="ss-tab" data-tab="#pane-portfolio"><i class="fas fa-folder-plus"></i> Portfolio</button>
        <button class="ss-tab" data-tab="#pane-edit"><i class="fas fa-cog"></i> Edit Profile</button>
    </div>

    <!-- ============== ABOUT ============== -->
    <div class="ss-tab-pane active" id="pane-about">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="ss-profile-section">
                    <h5><i class="fas fa-user"></i> About Me</h5>
                    <?php if (!empty($student['bio'])): ?>
                        <p style="color:var(--ss-text-secondary);line-height:1.7;margin:0;"><?= nl2br(htmlspecialchars($student['bio'])) ?></p>
                    <?php else: ?>
                        <p style="margin:0;color:var(--ss-text-3);">No bio added yet. Click <strong>Edit Profile</strong> to tell employers about yourself.</p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($student['skills_summary'])): ?>
                <div class="ss-profile-section">
                    <h5><i class="fas fa-list-check"></i> Skills Summary</h5>
                    <p style="color:var(--ss-text-secondary);line-height:1.7;margin:0;"><?= nl2br(htmlspecialchars($student['skills_summary'])) ?></p>
                </div>
                <?php endif; ?>

                <div class="ss-profile-section">
                    <h5><i class="fas fa-id-card"></i> Personal Information</h5>
                    <div class="row g-3" style="font-size:0.875rem;">
                        <?php
                        $infoRows = [
                            'First name'     => $firstName,
                            'Last name'      => $lastName,
                            'Email'          => $student['email'] ?? '',
                            'Phone'          => $student['phone'] ?? '',
                            'Department'     => $student['department'] ?? '',
                            'Year of study'  => !empty($student['year_of_study']) ? 'Year ' . (int)$student['year_of_study'] : '',
                            'GPA'            => $student['gpa'] ?? '',
                            'University'     => $student['uni_name'] ?? '',
                        ];
                        foreach ($infoRows as $label => $value):
                        ?>
                        <div class="col-sm-6">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span style="color:var(--ss-text-3);"><?= htmlspecialchars($label) ?></span>
                                <span class="fw-semibold"><?= htmlspecialchars($value ?: '—') ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ss-profile-section">
                    <h5><i class="fas fa-bullseye"></i> Profile Strength</h5>
                    <div class="text-center my-3">
                        <div class="ss-progress-circle mx-auto" style="width:130px;height:130px;">
                            <svg width="130" height="130" viewBox="0 0 130 130">
                                <defs>
                                    <linearGradient id="ss-grad-profile" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#4F46E5"/>
                                        <stop offset="100%" stop-color="#06B6D4"/>
                                    </linearGradient>
                                </defs>
                                <circle class="track" cx="65" cy="65" r="55" fill="none" stroke-width="10"/>
                                <circle cx="65" cy="65" r="55" fill="none" stroke="url(#ss-grad-profile)" stroke-width="10" stroke-linecap="round"
                                        stroke-dasharray="<?= 2 * M_PI * 55 ?>" stroke-dashoffset="<?= 2 * M_PI * 55 * (1 - $completion / 100) ?>"
                                        transform="rotate(-90 65 65)"/>
                            </svg>
                            <div class="pct"><?= $completion ?>%</div>
                        </div>
                        <div class="mt-3" style="font-size:0.85rem;color:var(--ss-text-secondary);">
                            <?php if ($completion >= 80): ?>
                                <?= Component::badge('Excellent', 'success') ?> Your profile stands out to employers.
                            <?php elseif ($completion >= 50): ?>
                                <?= Component::badge('Good progress', 'warning') ?> Add a few more details to stand out.
                            <?php else: ?>
                                <?= Component::badge('Needs work', 'danger') ?> Complete your profile to boost visibility.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="ss-profile-section">
                    <h5><i class="fas fa-link"></i> Quick Links</h5>
                    <div class="d-grid gap-2">
                        <a href="<?= URL::to('student/portfolio') ?>" class="ss-btn ss-btn-light text-start"><i class="fas fa-folder-plus me-2"></i> Manage Portfolio</a>
                        <a href="<?= URL::to('student/resume') ?>" class="ss-btn ss-btn-light text-start"><i class="fas fa-file-alt me-2"></i> Upload Resume</a>
                        <a href="<?= URL::to('student/ai-score') ?>" class="ss-btn ss-btn-light text-start"><i class="fas fa-robot me-2"></i> AI Resume Score</a>
                        <a href="<?= URL::to('student/settings') ?>" class="ss-btn ss-btn-light text-start"><i class="fas fa-cog me-2"></i> Account Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============== SKILLS ============== -->
    <div class="ss-tab-pane" id="pane-skills">
        <div class="ss-profile-section">
            <h5><i class="fas fa-code"></i> Technical Skills</h5>
            <?php if (!empty($skills)): ?>
                <div class="row g-3">
                    <?php foreach ($skills as $sk):
                        $pct = $proficiencyPct[$sk['proficiency_level'] ?? 'intermediate'] ?? 55;
                        $color = $pct >= 80 ? 'success' : ($pct >= 55 ? 'primary' : 'warning');
                    ?>
                    <div class="col-md-6">
                        <div class="skill-bar">
                            <div class="skill-head">
                                <span class="skill-name"><?= htmlspecialchars($sk['name'] ?? 'Skill') ?></span>
                                <span class="skill-level text-capitalize"><?= htmlspecialchars($sk['proficiency_level'] ?? 'intermediate') ?></span>
                            </div>
                            <?= Component::progress($pct, $color, 'sm') ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?= Component::emptyState([
                    'icon'   => 'fa-code',
                    'title'  => 'No skills added yet',
                    'desc'   => 'Add your technical skills to improve job matching and let employers find you faster.',
                    'action' => '<a href="#pane-edit" class="ss-btn ss-btn-soft" data-tab="#pane-edit"><i class="fas fa-plus"></i> Add Skills</a>'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============== EDUCATION ============== -->
    <div class="ss-tab-pane" id="pane-education">
        <div class="ss-profile-section">
            <h5><i class="fas fa-university"></i> Education History</h5>
            <?php if (!empty($education)): ?>
                <div class="ss-timeline">
                    <?php foreach ($education as $edu): ?>
                    <div class="ss-timeline-item info">
                        <div class="timeline-time">
                            <?= htmlspecialchars(date('M Y', strtotime($edu['start_date']))) ?> —
                            <?= !empty($edu['end_date']) ? htmlspecialchars(date('M Y', strtotime($edu['end_date']))) : 'Present' ?>
                        </div>
                        <div class="timeline-title"><?= htmlspecialchars($edu['degree'] ?? $edu['qualification'] ?? 'Degree') ?></div>
                        <div class="timeline-desc">
                            <?= htmlspecialchars($edu['institution'] ?? $edu['school_name'] ?? '') ?>
                            <?php if (!empty($edu['field_of_study'])): ?> · <?= htmlspecialchars($edu['field_of_study']) ?><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?= Component::emptyState([
                    'icon'  => 'fa-university',
                    'title' => 'No education records',
                    'desc'  => 'Add your educational background so employers can see your academic journey.'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============== EXPERIENCE ============== -->
    <div class="ss-tab-pane" id="pane-experience">
        <div class="ss-profile-section">
            <h5><i class="fas fa-briefcase"></i> Work Experience</h5>
            <?php if (!empty($experience)): ?>
                <div class="ss-timeline">
                    <?php foreach ($experience as $exp): ?>
                    <div class="ss-timeline-item success">
                        <div class="timeline-time">
                            <?= htmlspecialchars(date('M Y', strtotime($exp['start_date']))) ?> —
                            <?= !empty($exp['end_date']) ? htmlspecialchars(date('M Y', strtotime($exp['end_date']))) : 'Present' ?>
                        </div>
                        <div class="timeline-title"><?= htmlspecialchars($exp['job_title'] ?? $exp['title'] ?? 'Role') ?> · <?= htmlspecialchars($exp['company'] ?? '') ?></div>
                        <div class="timeline-desc"><?= htmlspecialchars($exp['description'] ?? '') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?= Component::emptyState([
                    'icon'  => 'fa-briefcase',
                    'title' => 'No experience added',
                    'desc'  => 'Internships, part-time roles and projects all count. Add them here to boost your profile.'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============== PORTFOLIO ============== -->
    <div class="ss-tab-pane" id="pane-portfolio">
        <div class="ss-profile-section">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-folder-plus"></i> Portfolio Projects</h5>
                <a href="<?= URL::to('student/portfolio') ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-arrow-right"></i> Manage Portfolio</a>
            </div>
            <?= Component::emptyState([
                'icon'   => 'fa-folder-open',
                'title'  => 'Showcase your work',
                'desc'   => 'Visit the portfolio page to add projects, screenshots and live links to impress recruiters.',
                'action' => '<a href="' . URL::to('student/portfolio') . '" class="ss-btn ss-btn-gradient"><i class="fas fa-plus"></i> Add Project</a>'
            ]) ?>
        </div>
    </div>

    <!-- ============== EDIT PROFILE ============== -->
    <div class="ss-tab-pane" id="pane-edit">
        <form method="POST" action="<?= URL::to('student/profile/update') ?>" enctype="multipart/form-data" data-validate>
            <?= $csrfField ?? '' ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Personal info -->
                    <div class="ss-card mb-4">
                        <div class="ss-card-header">
                            <h3><i class="fas fa-user text-primary"></i> Personal Information</h3>
                        </div>
                        <div class="ss-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <?= Component::floatField('first_name', 'First name *', 'text', $firstName, ['required' => true]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= Component::floatField('last_name', 'Last name *', 'text', $lastName, ['required' => true]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= Component::floatField('phone', 'Phone number', 'tel', $student['phone'] ?? '') ?>
                                </div>
                                <div class="col-md-6">
                                    <?= Component::floatField('department', 'Department', 'text', $student['department'] ?? '') ?>
                                </div>
                                <div class="col-md-6">
                                    <?= Component::floatField('year_of_study', 'Year of study', 'number', $student['year_of_study'] ?? '', ['attr' => 'min="1" max="8"']) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= Component::floatField('gpa', 'GPA (out of 4.0)', 'text', $student['gpa'] ?? '', ['attr' => 'step="0.01" min="0" max="4"']) ?>
                                </div>
                                <div class="col-12">
                                    <div class="ss-form-group">
                                        <label class="ss-form-label" for="bio">Bio <span style="color:var(--ss-text-3);font-weight:400;">— tell employers about yourself</span></label>
                                        <textarea name="bio" id="bio" class="ss-textarea" rows="4" placeholder="e.g. Final-year Computer Science student passionate about building scalable web apps and contributing to open-source projects."><?= htmlspecialchars($student['bio'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="ss-form-group">
                                        <label class="ss-form-label" for="skills_summary">Skills Summary <span style="color:var(--ss-text-3);font-weight:400;">— short paragraph highlighting your strengths</span></label>
                                        <textarea name="skills_summary" id="skills_summary" class="ss-textarea" rows="3" placeholder="e.g. Backend-focused developer with strong fundamentals in PHP, MySQL and REST API design. Comfortable with Git workflows and Agile teams."><?= htmlspecialchars($student['skills_summary'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social links -->
                    <div class="ss-card mb-4">
                        <div class="ss-card-header">
                            <h3><i class="fas fa-link text-primary"></i> Online Presence</h3>
                        </div>
                        <div class="ss-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="ss-input-icon">
                                        <i class="fab fa-linkedin" style="color:#0A66C2;"></i>
                                        <input type="url" name="linkedin" class="ss-input" placeholder="https://linkedin.com/in/username" value="<?= htmlspecialchars($student['linkedin'] ?? '') ?>" data-validate="url">
                                    </div>
                                    <div class="ss-form-hint">LinkedIn profile URL</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="ss-input-icon">
                                        <i class="fab fa-github"></i>
                                        <input type="url" name="github" class="ss-input" placeholder="https://github.com/username" value="<?= htmlspecialchars($student['github'] ?? '') ?>" data-validate="url">
                                    </div>
                                    <div class="ss-form-hint">GitHub profile URL</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="ss-input-icon">
                                        <i class="fas fa-globe"></i>
                                        <input type="url" name="website" class="ss-input" placeholder="https://yoursite.com" value="<?= htmlspecialchars($student['website'] ?? '') ?>" data-validate="url">
                                    </div>
                                    <div class="ss-form-hint">Personal website or blog</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="ss-card mb-4">
                        <div class="ss-card-header">
                            <h3><i class="fas fa-code text-primary"></i> Skills & Proficiency</h3>
                            <?= Component::badge(count($selectedSkills) . ' selected', 'primary') ?>
                        </div>
                        <div class="ss-card-body">
                            <div class="row g-2">
                                <?php foreach ($allSkills as $skill):
                                    $isChecked = isset($selectedSkills[$skill['id']]);
                                    $prof = $selectedSkills[$skill['id']] ?? 'intermediate';
                                ?>
                                <div class="col-md-6 col-xl-4">
                                    <label class="d-flex align-items-center gap-2 p-2 ss-rounded-md" style="border:1px solid var(--ss-border);cursor:pointer;transition:all .15s;" onmouseover="this.style.borderColor='var(--ss-primary)'" onmouseout="this.style.borderColor='var(--ss-border)'">
                                        <input type="checkbox" name="skills[]" value="<?= (int)$skill['id'] ?>" <?= $isChecked ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--ss-primary);">
                                        <span style="flex:1;font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($skill['name']) ?></span>
                                        <select name="proficiency_<?= (int)$skill['id'] ?>" class="ss-input ss-input-sm" style="width:auto;font-size:0.72rem;padding:0.25rem 0.5rem;">
                                            <option value="beginner" <?= $prof === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                            <option value="intermediate" <?= $prof === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                            <option value="advanced" <?= $prof === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                            <option value="expert" <?= $prof === 'expert' ? 'selected' : '' ?>>Expert</option>
                                        </select>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (empty($allSkills)): ?>
                                <div class="ss-form-hint text-center py-3">No skills defined in the system yet. Please contact the administrator.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: avatar + save -->
                <div class="col-lg-4">
                    <div class="ss-card ss-animate-fade-up" style="position:sticky;top:90px;">
                        <div class="ss-card-header">
                            <h3><i class="fas fa-image text-primary"></i> Profile Photo</h3>
                        </div>
                        <div class="ss-card-body text-center">
                            <div id="avatar-preview" class="ss-avatar ss-avatar-xl mx-auto mb-3" style="width:120px;height:120px;">
                                <?php if (!empty($userAvatar)): ?>
                                    <img src="<?= URL::asset($userAvatar) ?>" alt="Avatar">
                                <?php else: ?>
                                    <?= $initials ?: 'U' ?>
                                <?php endif; ?>
                            </div>
                            <label class="ss-file-upload mb-2" style="padding:1.25rem;cursor:pointer;">
                                <input type="file" name="avatar" accept="image/png,image/jpeg,image/gif" data-file-preview="#avatar-preview" style="display:none;">
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="upload-text">Click or drop photo</div>
                                <div class="upload-hint">JPG, PNG or GIF · Max 5MB</div>
                            </label>
                        </div>
                        <div class="ss-card-footer">
                            <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block"><i class="fas fa-save"></i> Save Profile</button>
                            <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-ghost ss-btn-block mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
