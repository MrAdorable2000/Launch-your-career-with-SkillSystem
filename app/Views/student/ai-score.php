<?php
/**
 * AI Resume Score — Detailed breakdown (v3)
 *
 * Data passed from InnovationController::aiScore():
 *   $student, $score (array with score, grade, breakdown [7 sub-arrays], suggestions [array of icon/text])
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'AI Resume Score';

$sc          = $score['score'] ?? 0;
$grade       = $score['grade'] ?? 'F';
$breakdown   = $score['breakdown'] ?? [];
$suggestions = $score['suggestions'] ?? [];
?>
<?= Component::pageHeader(
    'AI Resume Score',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>AI Resume Score</span>',
    '<a href="' . URL::to('student/profile') . '" class="ss-btn ss-btn-light"><i class="fas fa-pen"></i> Edit Profile</a>'
) ?>

<div class="ss-dashboard-grid">
    <div>
        <!-- Score Hero -->
        <div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
            <div class="ss-card-body text-center" style="padding:2.5rem;">
                <div style="font-size:0.85rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Overall Score</div>
                <div style="font-size:5rem;font-weight:900;line-height:1;color:#fff;margin:0.5rem 0;"><?= (int)$sc ?><span style="font-size:2rem;opacity:0.7;">/100</span></div>
                <div style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.25rem;background:rgba(255,255,255,0.2);border-radius:999px;font-weight:700;">
                    <i class="fas fa-trophy"></i> Grade <?= htmlspecialchars($grade) ?> — <?= $sc >= 80 ? 'Excellent' : ($sc >= 60 ? 'Good' : 'Needs Work') ?>
                </div>
                <p style="color:rgba(255,255,255,0.85);margin-top:1rem;font-size:0.9rem;max-width:480px;margin-left:auto;margin-right:auto;">
                    Our AI analyzed your profile across 7 dimensions to compute this score. Improve any area below to boost your ranking and visibility to employers.
                </p>
            </div>
        </div>

        <!-- Breakdown -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-chart-pie text-primary"></i> Score Breakdown</h3></div>
            <div class="ss-card-body">
                <?php foreach ($breakdown as $key => $b):
                    $pct = ($b['max'] ?? 0) > 0 ? round(($b['points'] ?? 0) / $b['max'] * 100) : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <div>
                            <span style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($b['label'] ?? ucfirst($key)) ?></span>
                            <?php if (!empty($b['count'])): ?>
                                <span class="ss-badge ss-badge-soft ms-2"><?= (int)$b['count'] ?> item<?= $b['count'] > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                        </div>
                        <span style="font-weight:700;font-size:0.9rem;color:var(--ss-text-2);"><?= (int)($b['points'] ?? 0) ?> / <?= (int)($b['max'] ?? 0) ?> pts</span>
                    </div>
                    <?= Component::progress($pct, $pct >= 80 ? 'success' : ($pct < 50 ? 'warning' : 'primary'), 'sm') ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Suggestions -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-lightbulb text-warning"></i> Personalized Suggestions</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-3">
                    <?php if (!empty($suggestions)): ?>
                        <?php foreach ($suggestions as $i => $s): ?>
                        <div class="d-flex gap-3 p-3" style="background:var(--ss-surface-2);border-radius:var(--ss-radius);">
                            <div style="width:40px;height:40px;border-radius:50%;background:var(--ss-primary-light);color:var(--ss-primary);display:inline-flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                                <i class="fas <?= htmlspecialchars($s['icon'] ?? 'fa-lightbulb') ?>"></i>
                            </div>
                            <div style="flex:1;font-size:0.875rem;color:var(--ss-text-2);line-height:1.5;">
                                <?= htmlspecialchars($s['text'] ?? '') ?>
                            </div>
                            <?= Component::badge('#' . ($i + 1), 'primary') ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4" style="color:var(--ss-text-3);font-size:0.875rem;">No suggestions yet — your profile looks great!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div>
        <!-- How it works -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-info-circle text-primary"></i> How Scoring Works</h3></div>
            <div class="ss-card-body">
                <p style="font-size:0.85rem;color:var(--ss-text-2);line-height:1.6;">Your AI Resume Score is computed using a rule-based algorithm that analyzes 7 dimensions of your profile:</p>
                <div class="d-flex flex-column gap-2 mt-3">
                    <?php
                    $factors = [
                        ['label' => 'Profile Completeness', 'max' => 30, 'icon' => 'fa-user-check',     'color' => 'primary'],
                        ['label' => 'Skills & Proficiency', 'max' => 20, 'icon' => 'fa-code',            'color' => 'info'],
                        ['label' => 'Education History',    'max' => 15, 'icon' => 'fa-graduation-cap',  'color' => 'success'],
                        ['label' => 'Work Experience',      'max' => 15, 'icon' => 'fa-briefcase',       'color' => 'warning'],
                        ['label' => 'Portfolio Projects',   'max' => 10, 'icon' => 'fa-folder-plus',     'color' => 'accent'],
                        ['label' => 'Verified Certificates','max' => 5,  'icon' => 'fa-certificate',     'color' => 'secondary'],
                        ['label' => 'Resume File Uploaded', 'max' => 5,  'icon' => 'fa-file-upload',     'color' => 'danger'],
                    ];
                    foreach ($factors as $f):
                    ?>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-soft-<?= $f['color'] ?>" style="width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">
                            <i class="fas <?= $f['icon'] ?>"></i>
                        </div>
                        <div style="flex:1;font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($f['label']) ?></div>
                        <?= Component::badge($f['max'] . ' pts', 'soft') ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="ss-alert ss-alert-info mt-3" style="font-size:0.78rem;">
                    <i class="fas fa-robot alert-icon"></i>
                    <div class="alert-body">This is a rule-based AI. The architecture supports swapping in an LLM API (OpenAI/Claude) later — see <code>app/Helpers/AiScorer.php</code>.</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-bolt text-primary"></i> Quick Actions</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2">
                    <a href="<?= URL::to('student/profile') ?>" class="ss-btn ss-btn-light ss-btn-block text-start"><i class="fas fa-user me-2"></i> Update Profile</a>
                    <a href="<?= URL::to('student/portfolio') ?>" class="ss-btn ss-btn-light ss-btn-block text-start"><i class="fas fa-folder-plus me-2"></i> Add Portfolio Project</a>
                    <a href="<?= URL::to('student/certificates') ?>" class="ss-btn ss-btn-light ss-btn-block text-start"><i class="fas fa-certificate me-2"></i> Add Certificate</a>
                    <a href="<?= URL::to('student/resume') ?>" class="ss-btn ss-btn-light ss-btn-block text-start"><i class="fas fa-file-upload me-2"></i> Upload Resume</a>
                    <a href="<?= URL::to('student/skill-gap') ?>" class="ss-btn ss-btn-gradient ss-btn-block"><i class="fas fa-bullseye me-2"></i> Skill Gap Analysis</a>
                </div>
            </div>
        </div>
    </div>
</div>
