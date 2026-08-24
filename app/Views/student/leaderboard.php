<?php
/**
 * Leaderboard — Student rankings (v3)
 *
 * Data passed from InnovationController::leaderboard():
 *   $top (array of student rows with score, skill_count, portfolio_count, application_count, cert_count,
 *        first_name, last_name, department, university),
 *   $myRank (array with rank, score, total),
 *   $student
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Leaderboard';

$myRank = $myRank ?? ['rank' => 0, 'score' => 0, 'total' => 0];
$top    = $top ?? [];
?>
<?= Component::pageHeader(
    'Student Leaderboard',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Leaderboard</span>',
    '<div class="ss-tabs-pills">' .
        '<button class="ss-tab-pill active" data-tab="#all-time">All Time</button>' .
        '<button class="ss-tab-pill" data-tab="#month">This Month</button>' .
        '<button class="ss-tab-pill" data-tab="#week">This Week</button>' .
    '</div>'
) ?>

<!-- Your rank card -->
<div class="ss-card ss-card-gradient mb-4 ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-4">
        <div style="text-align:center;">
            <div style="font-size:0.8rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Your Rank</div>
            <div style="font-size:3rem;font-weight:900;color:#fff;line-height:1;">#<?= (int)($myRank['rank'] ?? 0) ?></div>
            <div style="font-size:0.8rem;opacity:0.8;">of <?= (int)($myRank['total'] ?? 0) ?> students</div>
        </div>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:0.85rem;opacity:0.9;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Your Score</div>
            <div style="font-size:2rem;font-weight:800;color:#fff;line-height:1;margin:0.25rem 0;"><?= (int)($myRank['score'] ?? 0) ?> pts</div>
            <div style="font-size:0.8rem;opacity:0.85;">
                Score = profile + skills(×2) + portfolio(×3) + applications + certificates(×4) + experience(×2)
            </div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:0.8rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">To Top 10</div>
            <div style="font-size:1.5rem;font-weight:800;color:#fff;line-height:1;margin-top:4px;">
                <?php
                $toTop = 0;
                if (!empty($top) && ($myRank['rank'] ?? 0) > 10) {
                    $tenth = $top[9] ?? null;
                    if ($tenth) $toTop = max(0, (int)$tenth['score'] - (int)$myRank['score']);
                }
                ?>
                <?= $toTop > 0 ? '+' . $toTop . ' pts' : 'Achieved!' ?>
            </div>
        </div>
    </div>
</div>

<!-- Top 3 Podium -->
<?php if (!empty($top) && count($top) >= 3): ?>
<div class="row g-3 mb-4 ss-animate-fade-up">
    <?php
    $positions = [
        1 => ['height' => '120px', 'color' => 'var(--ss-gradient-warm)',                'medal' => '🥇'],
        0 => ['height' => '90px',  'color' => 'linear-gradient(135deg, #C0C0C0, #A8A8A8)', 'medal' => '🥈'],
        2 => ['height' => '70px',  'color' => 'linear-gradient(135deg, #CD7F32, #A0522D)', 'medal' => '🥉'],
    ];
    foreach ([1, 0, 2] as $idx):
        $s   = $top[$idx] ?? null;
        if (!$s) continue;
        $pos = $positions[$idx];
    ?>
    <div class="col-md-4">
        <div class="ss-card h-100 text-center" style="padding:1.5rem;">
            <div style="font-size:2.5rem;line-height:1;margin-bottom:0.5rem;"><?= $pos['medal'] ?></div>
            <div class="ss-avatar ss-avatar-xl mx-auto mb-2" style="background:<?= $pos['color'] ?>;">
                <?= strtoupper(substr($s['first_name'] ?? 'S', 0, 1)) ?>
            </div>
            <div style="font-weight:800;font-size:1rem;"><?= htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?></div>
            <div style="font-size:0.78rem;color:var(--ss-text-3);margin-bottom:0.5rem;"><?= htmlspecialchars($s['department'] ?? '') ?></div>
            <div style="font-weight:800;color:var(--ss-primary);font-size:1.5rem;"><?= (int)$s['score'] ?> <span style="font-size:0.78rem;color:var(--ss-text-3);">pts</span></div>
            <div style="height:<?= $pos['height'] ?>;background:<?= $pos['color'] ?>;border-radius:var(--ss-radius) var(--ss-radius) 0 0;margin-top:1rem;opacity:0.15;"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Full ranking list -->
<div class="ss-card ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-trophy text-primary"></i> Full Rankings</h3>
        <?= Component::badge(count($top) . ' students', 'primary') ?>
    </div>
    <div class="ss-card-body">
        <?php if (!empty($top)): ?>
        <div class="d-flex flex-column gap-2">
            <?php foreach ($top as $i => $s):
                $rank  = $i + 1;
                $isMe  = $student && (int)($s['id'] ?? 0) === (int)($student['id'] ?? 0);
            ?>
                <div class="ss-leaderboard-item <?= $rank <= 3 ? 'rank-' . $rank : '' ?> <?= $isMe ? 'ss-shadow-glow' : '' ?>" <?= $isMe ? 'style="border-color:var(--ss-primary);"' : '' ?>>
                    <div class="rank"><?= $rank ?></div>
                    <div class="ss-avatar ss-avatar-md"><?= strtoupper(substr($s['first_name'] ?? 'S', 0, 1)) ?></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:0.9rem;">
                            <?= htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?>
                            <?php if ($isMe): ?><?= Component::badge('You', 'primary') ?><?php endif; ?>
                        </div>
                        <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars($s['department'] ?? '') ?> · <?= htmlspecialchars($s['university'] ?? '') ?></div>
                    </div>
                    <div class="text-end d-none d-md-block">
                        <div style="font-size:0.7rem;color:var(--ss-text-3);">
                            <i class="fas fa-code"></i> <?= (int)($s['skill_count'] ?? 0) ?> ·
                            <i class="fas fa-folder-open"></i> <?= (int)($s['application_count'] ?? 0) ?> ·
                            <i class="fas fa-certificate"></i> <?= (int)($s['cert_count'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="score"><?= (int)$s['score'] ?> pts</div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <?= Component::emptyState(['icon' => 'fa-trophy', 'title' => 'No rankings yet', 'desc' => 'Be the first! Complete your profile and apply to jobs to climb the leaderboard.']) ?>
        <?php endif; ?>
    </div>
</div>
