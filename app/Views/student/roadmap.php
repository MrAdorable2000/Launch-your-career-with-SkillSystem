<?php
/**
 * Career Roadmap — Personalized milestones (v3)
 *
 * Data passed from InnovationController::roadmap():
 *   $student, $milestones (array of 7 milestone sub-arrays with title, desc, icon, status)
 *   status ∈ done|current|todo
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Career Roadmap';

$milestones   = $milestones ?? [];
$doneCount    = 0;
$currentCount = 0;
$totalCount   = count($milestones);
foreach ($milestones as $m) {
    if (($m['status'] ?? '') === 'done') $doneCount++;
    if (($m['status'] ?? '') === 'current') $currentCount++;
}
$progress = $totalCount > 0 ? round($doneCount / $totalCount * 100) : 0;
?>
<?= Component::pageHeader(
    'Career Roadmap',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Career Roadmap</span>',
    '<a href="' . URL::to('student/ai-score') . '" class="ss-btn ss-btn-light"><i class="fas fa-robot"></i> AI Score</a>'
) ?>

<!-- Progress banner -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
        <div class="ss-progress-circle" style="flex-shrink:0;">
            <svg width="120" height="120" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="10"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round"
                        stroke-dasharray="<?= 2 * M_PI * 50 ?>" stroke-dashoffset="<?= 2 * M_PI * 50 * (1 - $progress / 100) ?>"/>
            </svg>
            <div class="pct" style="color:#fff;"><?= $progress ?>%</div>
        </div>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:0.85rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;">Roadmap Progress</div>
            <h3 style="color:#fff;margin:0.25rem 0;font-size:1.4rem;"><?= $doneCount ?> of <?= $totalCount ?> milestones completed</h3>
            <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">
                <?php if ($currentCount > 0): ?>
                    You're currently working on the next milestone. Keep going!
                <?php elseif ($progress === 100): ?>
                    🎉 Congratulations! You've completed your roadmap!
                <?php else: ?>
                    Start with the first milestone below.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Milestones -->
<div class="ss-card ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-road text-primary"></i> Your Career Path</h3>
    </div>
    <div class="ss-card-body">
        <div class="ss-roadmap">
            <div class="ss-roadmap-track"></div>
            <?php foreach ($milestones as $i => $m):
                $status = $m['status'] ?? 'todo';
            ?>
                <div class="ss-roadmap-item <?= htmlspecialchars($status) ?>">
                    <div class="ss-roadmap-marker">
                        <?php if ($status === 'done'): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif ($status === 'current'): ?>
                            <i class="fas fa-dot-circle"></i>
                        <?php else: ?>
                            <span style="font-size:0.85rem;font-weight:700;"><?= $i + 1 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div style="flex:1;min-width:200px;">
                            <h6 style="margin-bottom:4px;">
                                <?= htmlspecialchars($m['title'] ?? '') ?>
                                <?php if ($status === 'done'): ?>
                                    <?= Component::badge('Done', 'success', 'fa-check') ?>
                                <?php elseif ($status === 'current'): ?>
                                    <?= Component::badge('In Progress', 'primary', 'fa-circle-notch fa-spin') ?>
                                <?php else: ?>
                                    <?= Component::badge('Locked', 'soft', 'fa-lock') ?>
                                <?php endif; ?>
                            </h6>
                            <p style="margin:0;color:var(--ss-text-2);"><?= htmlspecialchars($m['desc'] ?? '') ?></p>
                        </div>
                        <?php if ($status === 'current' || $status === 'todo'): ?>
                            <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-arrow-right"></i> Take Action</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Tips -->
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="ss-card h-100" style="padding:1.5rem;border-left:3px solid var(--ss-primary);">
            <h5 style="font-size:0.95rem;"><i class="fas fa-lightbulb text-primary me-1"></i> Why this roadmap?</h5>
            <p style="font-size:0.82rem;color:var(--ss-text-2);margin-top:0.5rem;">
                This personalized roadmap is generated based on your year of study, department, and current profile activity. Complete milestones in order — each unlocks the next stage of your career journey.
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ss-card h-100" style="padding:1.5rem;border-left:3px solid var(--ss-success);">
            <h5 style="font-size:0.95rem;"><i class="fas fa-trophy text-success me-1"></i> Reward system</h5>
            <p style="font-size:0.82rem;color:var(--ss-text-2);margin-top:0.5rem;">
                Each completed milestone contributes to your leaderboard score and may unlock achievement badges. The more milestones you complete, the higher you climb in the rankings.
            </p>
        </div>
    </div>
</div>
