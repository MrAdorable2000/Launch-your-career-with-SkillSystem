<?php
/**
 * Forum Topic — Single discussion view with comments (v3)
 *
 * Data passed from InnovationController::forumTopic():
 *   $topic (single row with title, body, category, tags, views_count,
 *           first_name, last_name, avatar, created_at),
 *   $comments (array with body, first_name, last_name, avatar, created_at)
 *
 * Form posts to student/forum/{id}/comment with field body.
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Discussion';

$topic    = $topic ?? [];
$comments = $comments ?? [];
?>
<?= Component::pageHeader(
    $topic['title'] ?? 'Discussion',
    '<a href="' . URL::to('student/forum') . '">Forum</a> / <span>Discussion</span>',
    '<a href="' . URL::to('student/forum') . '" class="ss-btn ss-btn-light"><i class="fas fa-arrow-left"></i> Back</a>'
) ?>

<div class="ss-dashboard-grid">
    <div>
        <!-- Original post -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-body">
                <div class="d-flex gap-3 mb-3">
                    <div class="ss-avatar ss-avatar-lg" style="background:var(--ss-gradient-primary);"><?= strtoupper(substr($topic['first_name'] ?? 'U', 0, 1)) ?></div>
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:0.95rem;"><?= htmlspecialchars(($topic['first_name'] ?? '') . ' ' . ($topic['last_name'] ?? '')) ?></div>
                        <div style="font-size:0.78rem;color:var(--ss-text-3);">
                            <?= Component::badge($topic['category'] ?? 'General', 'soft') ?>
                            <?= htmlspecialchars(!empty($topic['created_at']) ? date('M j, Y g:i A', strtotime($topic['created_at'])) : '') ?>
                        </div>
                    </div>
                </div>
                <div style="font-size:0.92rem;color:var(--ss-text-2);line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($topic['body'] ?? '') ?></div>
                <?php if (!empty($topic['tags'])): ?>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php foreach (explode(',', $topic['tags']) as $tag): ?>
                            <span class="ss-chip"><?= htmlspecialchars(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="d-flex gap-3 mt-3 pt-3" style="border-top:1px solid var(--ss-border);font-size:0.82rem;color:var(--ss-text-3);">
                    <span><i class="fas fa-eye"></i> <?= (int)($topic['views_count'] ?? 0) ?> views</span>
                    <span><i class="fas fa-comment"></i> <?= count($comments) ?> replies</span>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-comments text-primary"></i> Replies (<?= count($comments) ?>)</h3>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($comments)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($comments as $c): ?>
                            <div class="d-flex gap-3">
                                <div class="ss-avatar ss-avatar-md"><?= strtoupper(substr($c['first_name'] ?? 'U', 0, 1)) ?></div>
                                <div style="flex:1;">
                                    <div class="ss-card" style="padding:1rem;background:var(--ss-surface-2);">
                                        <div class="d-flex justify-content-between mb-1 flex-wrap gap-2">
                                            <div>
                                                <span style="font-weight:700;font-size:0.85rem;"><?= htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?></span>
                                                <span style="font-size:0.72rem;color:var(--ss-text-3);margin-left:6px;"><?= htmlspecialchars(!empty($c['created_at']) ? date('M j, g:i A', strtotime($c['created_at'])) : '') ?></span>
                                            </div>
                                        </div>
                                        <div style="font-size:0.875rem;color:var(--ss-text-2);line-height:1.5;white-space:pre-wrap;"><?= htmlspecialchars($c['body'] ?? '') ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-comment-slash mb-2 d-block" style="font-size:1.5rem;color:var(--ss-text-3);opacity:0.4;"></i>
                        <div style="font-size:0.85rem;color:var(--ss-text-3);">No replies yet. Be the first to respond!</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add comment -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-reply text-primary"></i> Add Your Reply</h3></div>
            <div class="ss-card-body">
                <form method="POST" action="<?= URL::to('student/forum/' . (int)($topic['id'] ?? 0) . '/comment') ?>">
                    <?= $csrfField ?? '' ?>
                    <div class="ss-form-group">
                        <textarea name="body" class="ss-textarea" required placeholder="Share your thoughts..."></textarea>
                    </div>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Post Reply</button>
                </form>
            </div>
        </div>
    </div>

    <div>
        <!-- Topic Info -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-info-circle text-primary"></i> Topic Info</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2" style="font-size:0.82rem;">
                    <?php
                    $infoRows = [
                        'Category' => $topic['category'] ?? 'General',
                        'Posted'   => !empty($topic['created_at']) ? date('M j, Y', strtotime($topic['created_at'])) : '—',
                        'Views'    => (int)($topic['views_count'] ?? 0),
                        'Replies'  => count($comments),
                    ];
                    foreach ($infoRows as $label => $value):
                    ?>
                    <div class="d-flex justify-content-between"><span style="color:var(--ss-text-3);"><?= htmlspecialchars($label) ?></span><span class="fw-bold"><?= htmlspecialchars((string)$value) ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Author -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-user text-primary"></i> Author</h3></div>
            <div class="ss-card-body text-center">
                <div class="ss-avatar ss-avatar-xl mx-auto mb-2" style="background:var(--ss-gradient-primary);"><?= strtoupper(substr($topic['first_name'] ?? 'U', 0, 1)) ?></div>
                <div style="font-weight:700;font-size:0.95rem;"><?= htmlspecialchars(($topic['first_name'] ?? '') . ' ' . ($topic['last_name'] ?? '')) ?></div>
                <div style="font-size:0.78rem;color:var(--ss-text-3);">Member since <?= htmlspecialchars(!empty($topic['created_at']) ? date('M Y', strtotime($topic['created_at'])) : '—') ?></div>
            </div>
        </div>
    </div>
</div>
