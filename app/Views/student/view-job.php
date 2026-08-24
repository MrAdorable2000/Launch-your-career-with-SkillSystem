<?php
/**
 * View Job — Premium redesign (v3)
 *
 * Data passed from StudentController::viewJob():
 *   $job        — job row joined with company + employer
 *   $hasApplied — boolean, whether the student already applied
 *
 * Form (when not applied) posts to student/jobs/{id}/apply with field cover_letter.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = $job['title'] ?? 'Job Details';

$job        = $job ?? [];
$hasApplied = $hasApplied ?? false;

$companyName    = $job['company_name'] ?? $job['employer_name'] ?? 'Company';
$companyLogo    = $job['company_logo'] ?? null;
$companyInitial = strtoupper(substr($companyName, 0, 1));
$deadline       = !empty($job['deadline']) ? date('M j, Y', strtotime($job['deadline'])) : 'Open';
$daysLeft       = !empty($job['deadline']) ? max(0, (int)ceil((strtotime($job['deadline']) - time()) / 86400)) : null;
$salary         = '';
if (!empty($job['salary_min'])) {
    $salary = number_format($job['salary_min']);
    if (!empty($job['salary_max'])) $salary .= ' – ' . number_format($job['salary_max']);
    $salary .= ' RWF';
}

// Parse requirements / responsibilities into bullet lists when newline-separated
$toList = function (?string $text): array {
    if (!$text) return [];
    $parts = preg_split('/\r\n|\r|\n/', trim($text));
    $parts = array_filter(array_map('trim', $parts), fn($x) => $x !== '');
    return $parts ?: [(string)$text];
};
$requirements     = $toList($job['requirements'] ?? null);
$responsibilities = $toList($job['responsibilities'] ?? null);

// Mock "similar jobs" — in a real app, the controller would pass these
$similarJobs = [
    ['title' => 'Junior Backend Engineer',  'company' => 'Andela',        'location' => 'Kigali', 'type' => 'full-time',  'match' => 92],
    ['title' => 'Frontend Developer Intern','company' => 'Irembo',         'location' => 'Remote', 'type' => 'internship', 'match' => 85],
    ['title' => 'Full-Stack Developer',     'company' => 'Bank of Kigali','location' => 'Kigali', 'type' => 'full-time',  'match' => 78],
];
?>
<?= Component::pageHeader(
    $job['title'] ?? 'Job Details',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <a href="' . URL::to('student/jobs') . '">Jobs</a> / <span>Job Details</span>',
    '<a href="' . URL::to('student/jobs') . '" class="ss-btn ss-btn-light"><i class="fas fa-arrow-left"></i> <span class="d-none d-md-inline">Back to Jobs</span></a>' .
    '<button class="ss-btn ss-btn-light" onclick="window.print()"><i class="fas fa-print"></i> <span class="d-none d-md-inline">Print</span></button>' .
    '<button class="ss-btn ss-btn-light" onclick="navigator.share?.({ title: \'' . htmlspecialchars($job['title'] ?? 'Job', ENT_QUOTES) . '\', url: window.location.href })"><i class="fas fa-share-alt"></i> <span class="d-none d-md-inline">Share</span></button>'
) ?>

<div class="row g-4">
    <!-- ============== MAIN COLUMN ============== -->
    <div class="col-lg-8">
        <!-- Hero card -->
        <div class="ss-card ss-animate-fade-up mb-4">
            <div class="ss-card-body">
                <div class="d-flex align-items-start gap-3 mb-4 flex-wrap">
                    <div style="width:72px;height:72px;border-radius:var(--ss-radius);background:var(--ss-gradient-soft);display:flex;align-items:center;justify-content:center;font-size:1.75rem;color:var(--ss-primary);font-weight:800;flex-shrink:0;overflow:hidden;">
                        <?php if (!empty($companyLogo)): ?>
                            <img src="<?= URL::asset($companyLogo) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <?= $companyInitial ?>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;min-width:200px;">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h2 class="mb-0" style="font-size:1.5rem;font-weight:800;"><?= htmlspecialchars($job['title'] ?? 'Untitled role') ?></h2>
                            <?php if (!empty($job['remote'])): ?>
                                <?= Component::badge('Remote', 'info', 'fa-wifi') ?>
                            <?php endif; ?>
                            <?php if (!empty($job['featured'])): ?>
                                <?= Component::badge('Featured', 'warning', 'fa-star') ?>
                            <?php endif; ?>
                        </div>
                        <div style="color:var(--ss-text-2);font-size:0.92rem;">
                            <i class="fas fa-building me-1 text-primary"></i> <?= htmlspecialchars($companyName) ?>
                            <?php if (!empty($job['company_location'])): ?>
                                · <i class="fas fa-map-marker-alt me-1 text-primary"></i> <?= htmlspecialchars($job['company_location']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <?php
                    $overview = [
                        ['label' => 'Location', 'icon' => 'fa-map-marker-alt', 'color' => 'var(--ss-danger)',  'value' => $job['location'] ?? 'Remote'],
                        ['label' => 'Type',     'icon' => 'fa-briefcase',      'color' => 'var(--ss-primary)', 'value' => ucfirst($job['type'] ?? 'Full-time')],
                        ['label' => 'Deadline', 'icon' => 'fa-clock',          'color' => 'var(--ss-warning)', 'value' => $deadline],
                        ['label' => 'Salary',   'icon' => 'fa-money-bill-wave','color' => $salary ? 'var(--ss-success)' : 'var(--ss-text-3)', 'value' => $salary ?: 'Negotiable'],
                    ];
                    foreach ($overview as $o):
                    ?>
                    <div class="col-6 col-md-3">
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--ss-text-3);font-weight:700;"><?= htmlspecialchars($o['label']) ?></div>
                        <div class="fw-semibold"><i class="fas <?= $o['icon'] ?> me-1" style="color:<?= $o['color'] ?>;"></i> <?= htmlspecialchars($o['value']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <span class="ss-chip"><i class="fas fa-eye"></i> <?= number_format((int)($job['views_count'] ?? 0)) ?> views</span>
                    <?php if ($daysLeft !== null): ?>
                        <span class="ss-chip <?= $daysLeft <= 3 ? 'ss-chip-primary' : '' ?>"><i class="fas fa-hourglass-half"></i> <?= $daysLeft === 0 ? 'Last day' : $daysLeft . ' days left' ?></span>
                    <?php endif; ?>
                    <span class="ss-chip"><i class="fas fa-calendar"></i> Posted <?= htmlspecialchars(!empty($job['created_at']) ? date('M j, Y', strtotime($job['created_at'])) : 'recently') ?></span>
                </div>
            </div>
        </div>

        <!-- Job description -->
        <div class="ss-card ss-animate-fade-up mb-4">
            <div class="ss-card-header"><h3><i class="fas fa-align-left text-primary"></i> Job Description</h3></div>
            <div class="ss-card-body">
                <div style="color:var(--ss-text-2);line-height:1.8;font-size:0.92rem;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($job['description'] ?? 'No description provided.')) ?></div>
            </div>
        </div>

        <!-- Requirements -->
        <?php if (!empty($requirements)): ?>
        <div class="ss-card ss-animate-fade-up mb-4">
            <div class="ss-card-header"><h3><i class="fas fa-list-check text-primary"></i> Requirements</h3></div>
            <div class="ss-card-body">
                <ul style="color:var(--ss-text-2);line-height:1.9;font-size:0.92rem;padding-left:1.25rem;margin:0;">
                    <?php foreach ($requirements as $req): ?>
                    <li><?= htmlspecialchars($req) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Responsibilities -->
        <?php if (!empty($responsibilities)): ?>
        <div class="ss-card ss-animate-fade-up mb-4">
            <div class="ss-card-header"><h3><i class="fas fa-tasks text-primary"></i> Responsibilities</h3></div>
            <div class="ss-card-body">
                <ul style="color:var(--ss-text-2);line-height:1.9;font-size:0.92rem;padding-left:1.25rem;margin:0;">
                    <?php foreach ($responsibilities as $res): ?>
                    <li><?= htmlspecialchars($res) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Apply form / status -->
        <?php if (!$hasApplied): ?>
        <div class="ss-card ss-animate-fade-up" id="apply">
            <div class="ss-card-header">
                <h3><i class="fas fa-paper-plane text-primary"></i> Apply for this Position</h3>
                <?= Component::badge('Applications open', 'success', 'fa-circle') ?>
            </div>
            <div class="ss-card-body">
                <form method="POST" action="<?= URL::to('student/jobs/' . (int)$job['id'] . '/apply') ?>" data-validate>
                    <?= $csrfField ?? '' ?>
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="cover_letter">Cover Letter <span style="color:var(--ss-text-3);font-weight:400;">— optional but recommended</span></label>
                        <textarea name="cover_letter" id="cover_letter" class="ss-textarea" rows="6" placeholder="Tell the employer why you're a great fit. Mention relevant projects, skills and what excites you about this role at <?= htmlspecialchars($companyName) ?>."></textarea>
                        <div class="ss-form-hint"><i class="fas fa-info-circle"></i> Tip: Keep it concise (3-4 short paragraphs). Address the hiring manager by name if you can.</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Submit Application</button>
                        <a href="<?= URL::to('student/resume') ?>" class="ss-btn ss-btn-light"><i class="fas fa-file-alt"></i> Update Resume First</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="ss-card ss-animate-fade-up" id="apply">
            <div class="ss-card-body text-center" style="padding:2.5rem 1.5rem;">
                <div class="ss-avatar ss-avatar-xl mx-auto mb-3" style="background:var(--ss-gradient-success);">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="mb-1">Application Submitted!</h3>
                <p style="color:var(--ss-text-3);margin-bottom:1rem;max-width:480px;margin-left:auto;margin-right:auto;">You've already applied for this position. We'll notify you when the employer reviews your application.</p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?= URL::to('student/applications') ?>" class="ss-btn ss-btn-gradient"><i class="fas fa-folder-open"></i> View My Applications</a>
                    <a href="<?= URL::to('student/jobs') ?>" class="ss-btn ss-btn-light"><i class="fas fa-search"></i> Browse More Jobs</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ============== SIDEBAR ============== -->
    <div class="col-lg-4">
        <!-- Apply CTA -->
        <div class="ss-card ss-card-gradient ss-animate-fade-up mb-4" style="position:sticky;top:90px;">
            <div class="ss-card-body">
                <h3 style="color:#fff;font-size:1.15rem;margin-bottom:0.5rem;">Ready to apply?</h3>
                <p style="color:rgba(255,255,255,0.85);font-size:0.85rem;margin-bottom:1rem;">
                    <?php if ($hasApplied): ?>
                        You've already applied for this role.
                    <?php elseif ($daysLeft !== null && $daysLeft <= 3): ?>
                        Only <strong><?= $daysLeft ?> days left</strong> to apply — don't miss out!
                    <?php else: ?>
                        Make sure your profile is up to date before applying.
                    <?php endif; ?>
                </p>
                <?php if (!$hasApplied): ?>
                <a href="#apply" class="ss-btn ss-btn-light ss-btn-block mb-2" style="background:rgba(255,255,255,0.2);color:#fff;border:none;"><i class="fas fa-paper-plane"></i> Apply Now</a>
                <?php else: ?>
                <a href="<?= URL::to('student/applications') ?>" class="ss-btn ss-btn-light ss-btn-block mb-2" style="background:rgba(255,255,255,0.2);color:#fff;border:none;"><i class="fas fa-folder-open"></i> Track Application</a>
                <?php endif; ?>
                <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-light ss-btn-block" style="background:rgba(255,255,255,0.1);color:#fff;border:none;"><i class="fas fa-user"></i> Preview My Profile</a>
            </div>
        </div>

        <!-- Job overview -->
        <div class="ss-card ss-animate-fade-up mb-4">
            <div class="ss-card-header"><h3>Job Overview</h3></div>
            <div class="ss-card-body" style="font-size:0.875rem;">
                <?php
                $overviewRows = [
                    ['icon' => 'fa-building',         'label' => 'Company',  'value' => $companyName, 'extra' => ''],
                    ['icon' => 'fa-briefcase',        'label' => 'Type',     'value' => ucfirst($job['type'] ?? 'Full-time'), 'extra' => 'text-capitalize'],
                    ['icon' => 'fa-map-marker-alt',   'label' => 'Location', 'value' => $job['location'] ?? 'Remote', 'extra' => ''],
                    ['icon' => 'fa-wifi',             'label' => 'Remote',   'value' => !empty($job['remote']) ? 'Yes' : 'No', 'extra' => ''],
                    ['icon' => 'fa-calendar',         'label' => 'Posted',   'value' => !empty($job['created_at']) ? date('M j, Y', strtotime($job['created_at'])) : '—', 'extra' => ''],
                    ['icon' => 'fa-clock',            'label' => 'Deadline', 'value' => $deadline, 'extra' => ($daysLeft !== null && $daysLeft <= 3) ? 'text-danger' : ''],
                ];
                if ($salary) {
                    array_splice($overviewRows, 4, 0, [['icon' => 'fa-money-bill-wave', 'label' => 'Salary', 'value' => $salary, 'extra' => 'text-success']]);
                }
                foreach ($overviewRows as $row):
                ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span style="color:var(--ss-text-3);"><i class="fas <?= $row['icon'] ?> me-1"></i> <?= htmlspecialchars($row['label']) ?></span>
                    <span class="fw-semibold text-end <?= $row['extra'] ?>"><?= htmlspecialchars($row['value']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Similar jobs -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3>Similar Jobs</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($similarJobs as $sj): ?>
                    <a href="<?= URL::to('student/jobs') ?>" class="d-flex align-items-start gap-2 text-decoration-none" style="color:inherit;">
                        <div class="ss-avatar ss-avatar-sm" style="background:var(--ss-gradient-cool);flex-shrink:0;"><?= strtoupper(substr($sj['company'], 0, 1)) ?></div>
                        <div style="flex:1;min-width:0;">
                            <div class="fw-semibold ss-clamp-2" style="font-size:0.85rem;"><?= htmlspecialchars($sj['title']) ?></div>
                            <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars($sj['company']) ?> · <?= htmlspecialchars($sj['location']) ?></div>
                        </div>
                        <span class="ss-badge ss-badge-success" style="font-size:0.65rem;"><?= $sj['match'] ?>%</span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <a href="<?= URL::to('student/jobs') ?>" class="ss-btn ss-btn-soft ss-btn-block mt-3"><i class="fas fa-search"></i> Browse All Jobs</a>
            </div>
        </div>
    </div>
</div>
