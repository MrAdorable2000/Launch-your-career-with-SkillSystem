<?php
/**
 * Forum — Discussion forum list (v3)
 *
 * Data passed from InnovationController::forum():
 *   $topics (array with title, body, category, tags, views_count, comment_count,
 *            first_name, last_name, avatar, created_at),
 *   $categories
 *
 * Form posts to student/forum/create with fields: title, body, category, tags
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Discussion Forum';

$topics     = $topics ?? [];
$categories = $categories ?? [];
?>
<?= Component::pageHeader(
    'Discussion Forum',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Discussion Forum</span>',
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#newTopicModal"><i class="fas fa-plus"></i> New Topic</button>'
) ?>

<!-- Stats -->
<div class="ss-stats-grid mb-4">
    <?= Component::statCard(['icon' => 'fa-comments', 'label' => 'Total Topics',  'count' => count($topics), 'color' => 'primary']) ?>
    <?= Component::statCard(['icon' => 'fa-folder',   'label' => 'Categories',    'count' => count($categories), 'color' => 'info']) ?>
    <?= Component::statCard(['icon' => 'fa-eye',      'label' => 'Total Views',   'count' => array_sum(array_column($topics, 'views_count')), 'color' => 'success']) ?>
    <?= Component::statCard(['icon' => 'fa-reply',    'label' => 'Total Replies', 'count' => array_sum(array_column($topics, 'comment_count')), 'color' => 'warning']) ?>
</div>

<div class="ss-dashboard-grid">
    <div>
        <!-- Topics list -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header">
                <h3><i class="fas fa-list text-primary"></i> Recent Discussions</h3>
            </div>
            <div class="ss-card-body">
                <?php if (!empty($topics)): ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($topics as $t):
                            $categoryColors = ['General' => 'primary', 'Career' => 'success', 'Technical' => 'info', 'Advice' => 'warning', 'Jobs' => 'accent'];
                            $catColor = $categoryColors[$t['category'] ?? 'General'] ?? 'soft';
                        ?>
                            <a href="<?= URL::to('student/forum/' . (int)$t['id']) ?>" class="text-decoration-none">
                                <div class="ss-card ss-hover-lift" style="padding:1rem;background:var(--ss-surface);">
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="ss-avatar ss-avatar-md"><?= strtoupper(substr($t['first_name'] ?? 'U', 0, 1)) ?></div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                <?= Component::badge($t['category'] ?? 'General', $catColor) ?>
                                                <span style="font-size:0.72rem;color:var(--ss-text-3);">by <?= htmlspecialchars(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')) ?> · <?= htmlspecialchars(date('M j', strtotime($t['created_at']))) ?></span>
                                            </div>
                                            <div style="font-weight:700;color:var(--ss-text);font-size:0.92rem;" class="ss-clamp-2"><?= htmlspecialchars($t['title'] ?? '') ?></div>
                                        </div>
                                        <div class="d-none d-md-flex flex-column align-items-end" style="font-size:0.75rem;color:var(--ss-text-3);">
                                            <div><i class="fas fa-comment me-1"></i> <?= (int)($t['comment_count'] ?? 0) ?> replies</div>
                                            <div><i class="fas fa-eye me-1"></i> <?= (int)($t['views_count'] ?? 0) ?> views</div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?= Component::emptyState([
                        'icon'   => 'fa-comments',
                        'title'  => 'No discussions yet',
                        'desc'   => 'Be the first to start a discussion! Ask a question, share advice, or start a debate.',
                        'action' => '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#newTopicModal"><i class="fas fa-plus"></i> Start a Discussion</button>'
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <!-- Categories -->
        <div class="ss-card mb-4 ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-folder text-primary"></i> Categories</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $allCats = ['General', 'Career', 'Technical', 'Advice', 'Jobs', 'Internships', 'Mentorship'];
                    foreach ($allCats as $cat):
                        $count = 0;
                        foreach ($topics as $t) { if (($t['category'] ?? '') === $cat) $count++; }
                    ?>
                        <a href="<?= URL::to('student/forum') ?>" class="ss-chip <?= $count > 0 ? 'ss-chip-primary' : '' ?>">
                            <?= htmlspecialchars($cat) ?> <span class="ss-badge ss-badge-soft ms-1"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Guidelines -->
        <div class="ss-card ss-animate-fade-up">
            <div class="ss-card-header"><h3><i class="fas fa-shield-alt text-primary"></i> Community Guidelines</h3></div>
            <div class="ss-card-body">
                <div class="d-flex flex-column gap-2" style="font-size:0.82rem;color:var(--ss-text-2);">
                    <div><i class="fas fa-check text-success me-2"></i> Be respectful and constructive</div>
                    <div><i class="fas fa-check text-success me-2"></i> Use clear, descriptive titles</div>
                    <div><i class="fas fa-check text-success me-2"></i> Tag with the right category</div>
                    <div><i class="fas fa-check text-success me-2"></i> No spam or self-promotion</div>
                    <div><i class="fas fa-check text-success me-2"></i> Mark solutions as answers</div>
                    <div><i class="fas fa-check text-success me-2"></i> Report inappropriate content</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Topic Modal -->
<div class="modal fade" id="newTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('student/forum/create') ?>" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-comments text-primary me-2"></i> Start a Discussion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ss-form-group mb-3">
                        <label class="ss-form-label">Title <span class="req">*</span></label>
                        <input type="text" name="title" class="ss-input" required placeholder="Be specific and descriptive">
                    </div>
                    <div class="ss-form-group mb-3">
                        <label class="ss-form-label">Category</label>
                        <select name="category" class="ss-select">
                            <?php foreach (['General', 'Career', 'Technical', 'Advice', 'Jobs', 'Internships', 'Mentorship'] as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ss-form-group mb-3">
                        <label class="ss-form-label">Tags (comma-separated)</label>
                        <input type="text" name="tags" class="ss-input" placeholder="e.g. internship, resume, technical">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Body <span class="req">*</span></label>
                        <textarea name="body" class="ss-textarea" required placeholder="Share your question, story, or insight..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Post Discussion</button>
                </div>
            </form>
        </div>
    </div>
</div>
