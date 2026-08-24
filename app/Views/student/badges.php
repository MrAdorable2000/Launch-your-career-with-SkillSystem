<?php
/**
 * Achievement Badges (v3)
 *
 * Data passed from InnovationController::badges():
 *   $student, $earned (array of earned badge rows with awarded_at),
 *   $all (all badge definitions), $earnedIds (flat array of earned badge IDs)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Achievement Badges';

$earned      = $earned ?? [];
$all         = $all ?? [];
$earnedIds   = $earnedIds ?? [];
$earnedCount = count($earned);
$totalCount  = count($all);
$totalPoints = array_sum(array_column($earned, 'points'));
?>
<?= Component::pageHeader(
    'Achievement Badges',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Badges</span>',
    '<a href="' . URL::to('student/leaderboard') . '" class="ss-btn ss-btn-light"><i class="fas fa-trophy"></i> Leaderboard</a>'
) ?>

<!-- Summary -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-medal',      'label' => 'Badges Earned',   'count' => $earnedCount, 'color' => 'warning']) ?>
    <?= Component::statCard(['icon' => 'fa-bullseye',   'label' => 'Total Available', 'count' => $totalCount, 'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-star',       'label' => 'Points Earned',   'count' => $totalPoints, 'color' => 'accent']) ?>
    <?= Component::statCard(['icon' => 'fa-percentage', 'label' => 'Completion',      'value' => $totalCount > 0 ? round($earnedCount / $totalCount * 100) . '%' : '0%', 'color' => 'success']) ?>
</div>

<!-- Earned Badges -->
<div class="ss-card mb-4 ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-trophy text-warning"></i> Earned Badges</h3>
        <?= Component::badge($earnedCount . ' unlocked', 'warning') ?>
    </div>
    <div class="ss-card-body">
        <?php if (!empty($earned)): ?>
            <div class="row g-3">
                <?php foreach ($earned as $b): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="ss-card ss-hover-lift h-100" style="padding:1.25rem;text-align:center;background:var(--ss-gradient-soft);">
                            <div style="width:80px;height:80px;border-radius:50%;background:var(--ss-gradient-warm);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:0.75rem;box-shadow:var(--ss-shadow-md);">
                                <i class="fas <?= htmlspecialchars($b['icon'] ?? 'fa-medal') ?>"></i>
                            </div>
                            <h5 style="font-size:0.95rem;margin-bottom:0.25rem;"><?= htmlspecialchars($b['name']) ?></h5>
                            <p style="font-size:0.8rem;color:var(--ss-text-2);margin-bottom:0.5rem;" class="ss-clamp-2"><?= htmlspecialchars($b['description'] ?? '') ?></p>
                            <div class="d-flex justify-content-center gap-2">
                                <?= Component::badge('+' . (int)$b['points'] . ' pts', 'warning', 'fa-star') ?>
                                <?= Component::badge('Earned', 'success', 'fa-check') ?>
                            </div>
                            <div style="font-size:0.72rem;color:var(--ss-text-3);margin-top:0.5rem;"><?= htmlspecialchars(!empty($b['awarded_at']) ? date('M j, Y', strtotime($b['awarded_at'])) : 'Recently') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?= Component::emptyState(['icon' => 'fa-medal', 'title' => 'No badges earned yet', 'desc' => 'Complete activities on the platform to earn your first badge!']) ?>
        <?php endif; ?>
    </div>
</div>

<!-- Locked Badges -->
<div class="ss-card ss-animate-fade-up">
    <div class="ss-card-header">
        <h3><i class="fas fa-lock text-muted"></i> Available Badges</h3>
        <?= Component::badge(max(0, $totalCount - $earnedCount) . ' locked', 'soft') ?>
    </div>
    <div class="ss-card-body">
        <?php if (!empty($all)): ?>
        <div class="row g-3">
            <?php foreach ($all as $b):
                $isEarned = in_array($b['id'], $earnedIds);
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="ss-card ss-hover-lift h-100 <?= $isEarned ? '' : 'ss-achievement locked' ?>" style="padding:1.25rem;text-align:center;<?= $isEarned ? 'opacity:0.6;' : '' ?>">
                        <div style="width:80px;height:80px;border-radius:50%;background:<?= $isEarned ? 'var(--ss-border-strong)' : 'var(--ss-gradient-warm)' ?>;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:0.75rem;">
                            <i class="fas <?= $isEarned ? 'fa-lock' : htmlspecialchars($b['icon'] ?? 'fa-medal') ?>"></i>
                        </div>
                        <h5 style="font-size:0.95rem;margin-bottom:0.25rem;"><?= htmlspecialchars($b['name']) ?></h5>
                        <p style="font-size:0.8rem;color:var(--ss-text-2);margin-bottom:0.5rem;" class="ss-clamp-2"><?= htmlspecialchars($b['description'] ?? '') ?></p>
                        <?= Component::badge('+' . (int)$b['points'] . ' pts', 'soft', 'fa-star') ?>
                        <div class="mt-2">
                            <?php if ($isEarned): ?>
                                <?= Component::badge('Already earned', 'success', 'fa-check') ?>
                            <?php else: ?>
                                <?= Component::badge('Locked', 'warning', 'fa-lock') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <?= Component::emptyState(['icon' => 'fa-lock', 'title' => 'No badges available', 'desc' => 'Check back soon — new badges are added regularly.']) ?>
        <?php endif; ?>
    </div>
</div>
