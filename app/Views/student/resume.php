<?php
/**
 * Resume — Premium redesign (v3)
 *
 * Data passed from StudentController::resume():
 *   $student, $resumes (array of resume rows: id, title, file_path, is_default, ai_score, created_at)
 *
 * Note: There is no upload endpoint yet in the controller; the form posts to student/resume/upload
 * which will need to be wired up by the backend team. The UI is complete and ready.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'My Resumes';

$student = $student ?? [];
$resumes = $resumes ?? [];

// Compute average AI score
$avgScore = 0;
if (!empty($resumes)) {
    $scores = array_filter(array_map(fn($r) => (int)($r['ai_score'] ?? 0), $resumes));
    if (!empty($scores)) $avgScore = (int)round(array_sum($scores) / count($scores));
}
?>
<?= Component::pageHeader(
    'My Resumes',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Resume</span>',
    '<a href="' . URL::to('student/ai-score') . '" class="ss-btn ss-btn-light"><i class="fas fa-robot"></i> <span class="d-none d-md-inline">AI Score</span></a>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#uploadResumeModal"><i class="fas fa-upload"></i> <span class="d-none d-md-inline">Upload Resume</span></button>'
) ?>

<!-- ============== STATS ============== -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-file-alt', 'label' => 'Total Resumes', 'count' => count($resumes), 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-star',     'label' => 'Avg AI Score', 'value' => $avgScore . '/100', 'color' => 'accent', 'trend' => $avgScore >= 70 ? 'Strong' : 'Improve', 'trendUp' => $avgScore >= 70]) ?>
    <?= Component::statCard(['icon' => 'fa-check',    'label' => 'Default Set',  'value' => (count(array_filter($resumes, fn($r) => !empty($r['is_default']))) > 0 ? 'Yes' : 'No'), 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-eye',      'label' => 'Profile Views (30d)', 'count' => 0, 'color' => 'info', 'trend' => 'Coming soon', 'trendUp' => true]) ?>
</div>

<div class="row g-4">
    <!-- ============== RESUMES LIST ============== -->
    <div class="col-lg-8">
        <?php if (empty($resumes)): ?>
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-body">
                <?= Component::emptyState([
                    'icon'   => 'fa-file-pdf',
                    'title'  => 'No resumes uploaded yet',
                    'desc'   => 'Upload your existing resume (PDF, DOC or DOCX) or build a new one from your profile. Your default resume is automatically attached to job applications.',
                    'action' => '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#uploadResumeModal"><i class="fas fa-upload"></i> Upload Your First Resume</button>'
                ]) ?>
            </div>
        </div>
        <?php else: ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0" style="font-size:1.1rem;font-weight:700;"><i class="fas fa-folder-open text-primary me-1"></i> Uploaded Resumes</h3>
            <span class="ss-badge ss-badge-soft"><?= count($resumes) ?> file<?= count($resumes) === 1 ? '' : 's' ?></span>
        </div>

        <div class="row g-3">
            <?php foreach ($resumes as $i => $r):
                $score     = (int)($r['ai_score'] ?? 0);
                $scoreColor = $score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger'));
                $isDefault = !empty($r['is_default']);
                $ext       = strtolower(pathinfo($r['file_path'] ?? $r['title'] ?? '', PATHINFO_EXTENSION)) ?: 'pdf';
                $icon      = match($ext) { 'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word', default => 'fa-file-alt' };
                $iconColor = match($ext) { 'pdf' => 'var(--ss-danger)', 'doc' => '#2563EB', 'docx' => '#2563EB', default => 'var(--ss-primary)' };
            ?>
            <div class="col-md-6 ss-animate-fade-up ss-delay-<?= (string)(($i % 4) + 1) ?>">
                <div class="ss-card ss-hover-lift h-100" <?= $isDefault ? 'style="border-color:var(--ss-primary);border-width:2px;"' : '' ?>>
                    <div class="ss-card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div style="width:48px;height:48px;border-radius:var(--ss-radius-sm);background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;color:<?= $iconColor ?>;font-size:1.4rem;flex-shrink:0;">
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="fw-semibold ss-truncate" title="<?= htmlspecialchars($r['title'] ?? 'Resume') ?>"><?= htmlspecialchars($r['title'] ?? 'Untitled Resume') ?></div>
                                <div style="font-size:0.75rem;color:var(--ss-text-3);">
                                    <?= strtoupper($ext) ?> · <?= htmlspecialchars(date('M j, Y', strtotime($r['created_at']))) ?>
                                </div>
                            </div>
                            <?php if ($isDefault): ?>
                            <?= Component::badge('Default', 'success', 'fa-check') ?>
                            <?php endif; ?>
                        </div>

                        <!-- AI Score -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1" style="font-size:0.78rem;">
                                <span style="color:var(--ss-text-3);"><i class="fas fa-robot me-1"></i> AI Score</span>
                                <span class="fw-bold" style="color:var(--ss-<?= $scoreColor ?>);"><?= $score ?>/100</span>
                            </div>
                            <?= Component::progress($score, $scoreColor, 'sm') ?>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?= !empty($r['file_path']) ? URL::asset($r['file_path']) : '#' ?>" class="ss-btn ss-btn-soft ss-btn-sm flex-fill" download><i class="fas fa-download"></i> Download</a>
                            <?php if (!$isDefault): ?>
                            <form method="POST" action="<?= URL::to('student/resume/default') ?>" style="display:inline;">
                                <?= $csrfField ?? '' ?>
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="ss-btn ss-btn-light ss-btn-sm" title="Set as default"><i class="fas fa-star"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= URL::to('student/resume/delete') ?>" onsubmit="return confirm('Delete this resume? This cannot be undone.');" style="display:inline;">
                                <?= $csrfField ?? '' ?>
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="ss-btn ss-btn-light ss-btn-sm" title="Delete" style="color:var(--ss-danger);"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Upload drop zone (always visible) -->
        <div class="ss-card ss-animate-fade-up mt-4">
            <div class="ss-card-header">
                <h3><i class="fas fa-cloud-upload-alt text-primary"></i> Upload a New Resume</h3>
            </div>
            <div class="ss-card-body">
                <form method="POST" action="<?= URL::to('student/resume/upload') ?>" enctype="multipart/form-data" data-validate id="resumeUploadForm">
                    <?= $csrfField ?? '' ?>
                    <label class="ss-file-upload" style="cursor:pointer;">
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" data-file-preview="#resume-file-preview" required style="display:none;">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Drop your resume here, or click to browse</div>
                        <div class="upload-hint">PDF, DOC or DOCX · Max 10MB</div>
                    </label>
                    <div id="resume-file-preview" style="display:none;margin-top:0.75rem;"></div>

                    <?= Component::floatField('title', 'Resume title (e.g. "Backend Developer Resume 2025")', 'text', null, ['id' => 'resume-title']) ?>

                    <label class="ss-check mt-2">
                        <input type="checkbox" name="is_default" value="1">
                        <span>Set as default resume (used for new applications)</span>
                    </label>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-upload"></i> Upload Resume</button>
                        <button type="reset" class="ss-btn ss-btn-light" onclick="document.getElementById('resume-file-preview').style.display='none';">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============== SIDEBAR ============== -->
    <div class="col-lg-4">
        <!-- Build resume CTA -->
        <div class="ss-card ss-card-gradient ss-animate-fade-up mb-4">
            <div class="ss-card-body">
                <div class="text-center mb-3">
                    <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);display:inline-flex;align-items:center;justify-content:center;font-size:1.75rem;color:#fff;margin-bottom:0.75rem;">
                        <i class="fas fa-magic"></i>
                    </div>
                    <h3 style="color:#fff;font-size:1.2rem;margin-bottom:0.4rem;">Build a Resume in Minutes</h3>
                    <p style="color:rgba(255,255,255,0.85);font-size:0.85rem;margin:0;">Let our builder assemble a polished resume from your profile — no design skills needed.</p>
                </div>
                <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-light ss-btn-block" style="background:rgba(255,255,255,0.2);color:#fff;border:none;"><i class="fas fa-rocket"></i> Start Building</a>
            </div>
        </div>

        <!-- AI score card -->
        <div class="ss-card ss-animate-fade-up mb-4">
            <div class="ss-card-header">
                <h3><i class="fas fa-robot text-primary"></i> AI Resume Analysis</h3>
            </div>
            <div class="ss-card-body text-center">
                <?php if ($avgScore > 0): ?>
                <div class="ss-progress-circle mx-auto mb-3" style="width:120px;height:120px;">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <defs>
                            <linearGradient id="ss-grad-resume" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4F46E5"/>
                                <stop offset="100%" stop-color="#06B6D4"/>
                            </linearGradient>
                        </defs>
                        <circle class="track" cx="60" cy="60" r="50" fill="none" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke="url(#ss-grad-resume)" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="<?= 2 * M_PI * 50 ?>" stroke-dashoffset="<?= 2 * M_PI * 50 * (1 - $avgScore / 100) ?>"
                                transform="rotate(-90 60 60)"/>
                    </svg>
                    <div class="pct"><?= $avgScore ?></div>
                </div>
                <div style="font-size:0.85rem;color:var(--ss-text-2);">Average AI score across your resumes</div>
                <?php else: ?>
                <div style="color:var(--ss-text-3);padding:1.5rem 0;"><i class="fas fa-robot d-block mb-2" style="font-size:2rem;opacity:0.4;"></i>Upload a resume to get an AI score</div>
                <?php endif; ?>
                <a href="<?= URL::to('student/ai-score') ?>" class="ss-btn ss-btn-soft ss-btn-block mt-3"><i class="fas fa-arrow-right"></i> View Detailed Analysis</a>
            </div>
        </div>

        <!-- Tips -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-lightbulb text-primary"></i> Quick Tips</h3>
            </div>
            <div class="ss-card-body" style="font-size:0.85rem;">
                <?php
                $tips = [
                    'Use <strong>PDF format</strong> for cross-platform compatibility.',
                    'Keep it to <strong>1-2 pages</strong> for early-career roles.',
                    'Include <strong>quantified achievements</strong> (e.g. "Reduced load time by 40%").',
                    'Always <strong>tailor your resume</strong> for each application.',
                ];
                foreach ($tips as $t):
                ?>
                <div class="d-flex gap-2 mb-2">
                    <i class="fas fa-check text-success mt-1" style="font-size:0.7rem;"></i>
                    <span><?= $t ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============== UPLOAD MODAL (alternative entry point) ============== -->
<div class="modal fade" id="uploadResumeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload text-primary me-1"></i> Upload Resume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= URL::to('student/resume/upload') ?>" enctype="multipart/form-data" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <label class="ss-file-upload" style="cursor:pointer;">
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" data-file-preview="#modal-resume-preview" required style="display:none;">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Click or drop your file here</div>
                        <div class="upload-hint">PDF, DOC or DOCX · Max 10MB</div>
                    </label>
                    <div id="modal-resume-preview" style="display:none;margin-top:0.75rem;"></div>
                    <?= Component::floatField('title', 'Resume title', 'text', null, ['id' => 'modal-resume-title']) ?>
                    <label class="ss-check mt-2">
                        <input type="checkbox" name="is_default" value="1">
                        <span>Set as default resume</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-upload"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
