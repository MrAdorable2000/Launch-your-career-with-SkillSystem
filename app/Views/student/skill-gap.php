<?php
/**
 * Skill Gap Analysis (v3)
 *
 * Data passed from InnovationController::skillGap():
 *   $student, $analysis (array with target_role, current_skills, matched, missing, coverage_pct),
 *   $targetRole
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Skill Gap Analysis';

$coverage = $analysis['coverage_pct'] ?? 0;
$matched  = $analysis['matched'] ?? [];
$missing  = $analysis['missing'] ?? [];
$current  = $analysis['current_skills'] ?? [];
?>
<?= Component::pageHeader(
    'Skill Gap Analysis',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Skill Gap Analysis</span>',
    '<a href="' . URL::to('student/profile') . '" class="ss-btn ss-btn-light"><i class="fas fa-pen"></i> Edit Skills</a>'
) ?>

<!-- Search by role -->
<div class="ss-card mb-4 ss-animate-fade-up">
    <div class="ss-card-body">
        <form method="GET" action="<?= URL::to('student/skill-gap') ?>" class="d-flex gap-2 flex-wrap align-items-end">
            <div style="flex:1;min-width:240px;">
                <label class="ss-form-label">Target Role / Job Title</label>
                <input type="text" name="role" class="ss-input" placeholder="e.g. Software Engineer, Data Analyst, Marketing Intern" value="<?= htmlspecialchars($targetRole ?? '') ?>">
            </div>
            <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-search"></i> Analyze</button>
            <a href="<?= URL::to('student/skill-gap') ?>" class="ss-btn ss-btn-light">Reset</a>
        </form>
    </div>
</div>

<!-- Coverage -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body text-center" style="padding:2rem;">
        <div style="font-size:0.85rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Skill Coverage</div>
        <div style="font-size:4rem;font-weight:900;line-height:1;color:#fff;margin:0.5rem 0;"><?= (int)$coverage ?>%</div>
        <div style="color:rgba(255,255,255,0.85);font-size:0.9rem;">
            <?php if (!empty($targetRole)): ?>
                Based on jobs matching "<?= htmlspecialchars($targetRole) ?>"
            <?php else: ?>
                Based on all published jobs in the platform
            <?php endif; ?>
        </div>
        <div class="ss-progress ss-progress-lg mt-3" style="background:rgba(255,255,255,0.2);max-width:400px;margin:1rem auto 0;">
            <div class="ss-progress-bar" style="width:<?= (int)$coverage ?>%;background:#fff;"></div>
        </div>
    </div>
</div>

<div class="ss-dashboard-grid">
    <div>
        <!-- Matched Skills -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-check-circle text-success"></i> Skills You Have (<?= count($matched) ?>)</h3>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($matched)): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($matched as $s):
                            $lvl = ['beginner' => 'soft', 'intermediate' => 'info', 'advanced' => 'success', 'expert' => 'primary'];
                            // find proficiency from current skills
                            $prof = 'intermediate';
                            foreach ($current as $c) { if (strtolower($c['name']) === strtolower($s['name'])) { $prof = $c['proficiency_level']; break; } }
                        ?>
                            <div class="ss-chip ss-chip-primary">
                                <i class="fas fa-check"></i>
                                <?= htmlspecialchars($s['name'] ?? '') ?>
                                <span class="ss-badge ss-badge-<?= $lvl[$prof] ?? 'soft' ?>" style="margin-left:4px;"><?= htmlspecialchars($prof) ?></span>
                                <span class="ss-badge ss-badge-soft" style="margin-left:4px;"><?= (int)($s['demand'] ?? 0) ?> jobs</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState(['icon' => 'fa-check-circle', 'title' => 'No matches yet', 'desc' => 'Add more skills to your profile to see what employers are looking for.']) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Missing Skills -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-exclamation-triangle text-warning"></i> Skills to Learn (<?= count($missing) ?>)</h3>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($missing)): ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($missing as $s): ?>
                            <div class="d-flex align-items-center gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-radius);">
                                <div style="width:40px;height:40px;border-radius:8px;background:var(--ss-warning-light);color:var(--ss-warning);display:inline-flex;align-items:center;justify-content:center;font-size:1rem;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($s['name'] ?? '') ?></div>
                                    <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars($s['category'] ?? 'General') ?></div>
                                </div>
                                <div class="text-end">
                                    <div style="font-size:0.75rem;color:var(--ss-text-3);">Required in</div>
                                    <div style="font-weight:700;color:var(--ss-warning);"><?= (int)($s['demand'] ?? 0) ?> jobs</div>
                                </div>
                                <a href="https://www.google.com/search?q=<?= urlencode('learn ' . ($s['name'] ?? '')) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-external-link-alt"></i> Learn</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-trophy mb-2 d-block" style="font-size:2rem;color:var(--ss-success);"></i>
                        <div style="font-size:0.9rem;font-weight:600;color:var(--ss-text);">You have all the in-demand skills!</div>
                        <div style="font-size:0.8rem;color:var(--ss-text-3);">Keep updating your profile to stay ahead.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <!-- Current Skills Summary -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-list text-primary"></i> Your Current Skills</h3></div>
            <div class="ss-card-body">
                <?php if (!empty($current)): ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($current as $s): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-code text-primary"></i>
                                <div style="flex:1;font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($s['name'] ?? '') ?></div>
                                <span class="ss-badge ss-badge-soft text-capitalize"><?= htmlspecialchars($s['proficiency_level'] ?? 'intermediate') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <div style="font-size:0.85rem;color:var(--ss-text-3);">No skills added yet.</div>
                        <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-soft ss-btn-sm mt-2">Add Skills</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tip -->
        <div class="ss-card ss-card-gradient ss-animate-fade-up">
            <div class="ss-card-body">
                <div style="font-size:0.85rem;opacity:0.9;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;"><i class="fas fa-lightbulb"></i> Pro Tip</div>
                <p style="color:#fff;font-size:0.9rem;margin-top:0.5rem;line-height:1.5;">
                    Focus on learning the top 3 missing skills first. Each new skill you add can increase your job match rate by 15-25%.
                </p>
                <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-light ss-btn-block mt-2" style="background:rgba(255,255,255,0.2);color:#fff;border:none;">
                    <i class="fas fa-plus"></i> Add New Skill
                </a>
            </div>
        </div>
    </div>
</div>
