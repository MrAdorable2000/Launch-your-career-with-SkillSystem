<?php
/**
 * Admin — Homepage Content Manager
 * Data: $sections (grouped by type), $allContent
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;

$pageTitle = 'Homepage Manager';

$sectionMeta = [
    'hero' => ['label' => 'Hero Section', 'icon' => 'fa-star', 'color' => 'primary', 'desc' => 'Main banner at the top of the homepage'],
    'announcement' => ['label' => 'Announcements', 'icon' => 'fa-bullhorn', 'color' => 'warning', 'desc' => 'Banner announcements and news'],
    'video' => ['label' => 'YouTube Videos', 'icon' => 'fab fa-youtube', 'color' => 'danger', 'desc' => 'Embedded YouTube videos'],
    'event' => ['label' => 'Events', 'icon' => 'fa-calendar-alt', 'color' => 'info', 'desc' => 'Upcoming events and workshops'],
    'testimonial' => ['label' => 'Testimonials', 'icon' => 'fa-quote-right', 'color' => 'success', 'desc' => 'Student and employer success stories'],
    'custom' => ['label' => 'Custom Content', 'icon' => 'fa-puzzle-piece', 'color' => 'accent', 'desc' => 'Custom content blocks'],
];
?>

<?= Component::pageHeader(
    'Homepage Manager 🏠',
    '<a href="' . URL::to('admin/dashboard') . '">Admin</a> / <span>Homepage</span>',
    '<a href="' . URL::to('/') . '" target="_blank" class="ss-btn ss-btn-light"><i class="fas fa-external-link-alt"></i> View Homepage</a>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addContentModal"><i class="fas fa-plus"></i> Add Content</button>'
) ?>

<!-- ==================== QUICK STATS ==================== -->
<div class="ss-stats-grid mb-4">
    <?php
    $totalItems = 0;
    $activeItems = 0;
    foreach ($sections as $sec => $items) {
        $totalItems += count($items);
        foreach ($items as $item) if (!empty($item['is_active'])) $activeItems++;
    }
    ?>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-primary"><i class="fas fa-list"></i></div>
        <div class="stat-value"><?= $totalItems ?></div>
        <div class="stat-label">Total Items</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-success"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?= $activeItems ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-warning"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-value"><?= $totalItems - $activeItems ?></div>
        <div class="stat-label">Inactive</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-info"><i class="fas fa-layer-group"></i></div>
        <div class="stat-value"><?= count(array_filter($sections, fn($s) => !empty($s))) ?></div>
        <div class="stat-label">Active Sections</div>
    </div>
</div>

<!-- ==================== CONTENT SECTIONS ==================== -->
<?php foreach ($sectionMeta as $secKey => $secInfo): ?>
<div class="ss-card mb-4 ss-animate-fade-up">
    <div class="ss-card-header">
        <h3>
            <i class="<?= htmlspecialchars($secInfo['icon']) ?>" style="color:var(--ss-<?= $secInfo['color'] ?>);"></i>
            <?= htmlspecialchars($secInfo['label']) ?>
            <span class="ss-badge ss-badge-<?= $secInfo['color'] ?> ms-2"><?= count($sections[$secKey] ?? []) ?></span>
        </h3>
        <button class="ss-btn ss-btn-soft ss-btn-sm" onclick="document.getElementById('addSection_select').value='<?= $secKey ?>'; document.getElementById('addContentModal').querySelector('.modal-title').textContent='Add <?= htmlspecialchars($secInfo['label']) ?>'; new bootstrap.Modal(document.getElementById('addContentModal')).show();">
            <i class="fas fa-plus"></i> Add <?= htmlspecialchars($secInfo['label']) ?>
        </button>
    </div>
    <div class="ss-card-body">
        <?php if (!empty($sections[$secKey])): ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($sections[$secKey] as $item): ?>
                <div class="ss-card-hover" style="border:1px solid var(--ss-border);border-radius:var(--ss-r);padding:1rem;display:flex;align-items:flex-start;gap:1rem;">
                    <!-- Icon -->
                    <div style="width:44px;height:44px;border-radius:var(--ss-r-sm);background:var(--ss-<?= $secInfo['color'] ?>-light);color:var(--ss-<?= $secInfo['color'] ?>);display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                        <i class="<?= htmlspecialchars($secInfo['icon']) ?>"></i>
                    </div>

                    <!-- Content -->
                    <div style="flex:1;min-width:0;">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span style="font-size:0.88rem;font-weight:700;"><?= htmlspecialchars($item['title'] ?? 'Untitled') ?></span>
                            <?= !empty($item['is_active']) ? '<span class="ss-badge ss-badge-success"><i class="fas fa-check"></i> Active</span>' : '<span class="ss-badge ss-badge-soft"><i class="fas fa-pause"></i> Inactive</span>' ?>
                            <span class="ss-badge ss-badge-soft">Order: <?= (int)($item['sort_order'] ?? 0) ?></span>
                        </div>
                        <?php if (!empty($item['subtitle'])): ?>
                            <div style="font-size:0.8rem;color:var(--ss-text-2);margin-bottom:4px;"><?= htmlspecialchars($item['subtitle']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['body'])): ?>
                            <div style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars(substr($item['body'], 0, 120)) ?><?= strlen($item['body'] ?? '') > 120 ? '...' : '' ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['video_url'])): ?>
                            <div style="font-size:0.75rem;color:var(--ss-danger);margin-top:4px;"><i class="fab fa-youtube"></i> <?= htmlspecialchars($item['video_url']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['link_url'])): ?>
                            <div style="font-size:0.75rem;color:var(--ss-primary);margin-top:4px;"><i class="fas fa-link"></i> <?= htmlspecialchars($item['link_url']) ?> → <?= htmlspecialchars($item['link_text'] ?? 'Link') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['image_url'])): ?>
                            <div style="font-size:0.75rem;color:var(--ss-info);margin-top:4px;"><i class="fas fa-image"></i> <?= htmlspecialchars($item['image_url']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="ss-btn ss-btn-ghost ss-btn-sm" onclick="editContent(<?= (int)$item['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                        <form method="POST" action="<?= URL::to('admin/homepage/' . (int)$item['id'] . '/delete') ?>" style="display:inline;" onsubmit="return confirm('Delete this item?');">
                            <?= $csrfField ?? '' ?>
                            <button type="submit" class="ss-btn ss-btn-ghost ss-btn-sm" style="color:var(--ss-danger);" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="padding:1.5rem;text-align:center;color:var(--ss-text-3);">
                <i class="<?= htmlspecialchars($secInfo['icon']) ?> mb-2 d-block" style="font-size:1.5rem;opacity:0.3;"></i>
                <div style="font-size:0.85rem;"><?= htmlspecialchars($secInfo['desc']) ?></div>
                <div style="font-size:0.78rem;margin-top:4px;">No items yet — click "Add" to create one.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- ==================== ADD CONTENT MODAL ==================== -->
<div class="modal fade" id="addContentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('admin/homepage/add') ?>">
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus text-primary"></i> Add Homepage Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Section *</label>
                                <select name="section" class="ss-select" required id="addSection_select">
                                    <option value="hero">⭐ Hero Section</option>
                                    <option value="announcement">📢 Announcement</option>
                                    <option value="video">▶️ YouTube Video</option>
                                    <option value="event">📅 Event</option>
                                    <option value="testimonial">💬 Testimonial</option>
                                    <option value="custom">🧩 Custom Content</option>
                                </select>
                                <?php // BUG FIX: Removed the duplicate hidden input with name="section" — it was
                                      // overriding the select value with an empty string, causing all admin posts
                                      // to be saved with section="" and never appear on the homepage. ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="ss-input" value="1" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Title *</label>
                        <input type="text" name="title" class="ss-input" placeholder="Enter title" required>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Subtitle</label>
                        <input type="text" name="subtitle" class="ss-input" placeholder="Subtitle or short description">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Body Text</label>
                        <textarea name="body" class="ss-textarea" rows="3" placeholder="Full description or content"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">YouTube URL</label>
                                <input type="url" name="video_url" class="ss-input" placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Image URL</label>
                                <input type="url" name="image_url" class="ss-input" placeholder="https://...image.jpg">
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Link URL</label>
                                <input type="url" name="link_url" class="ss-input" placeholder="https://...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Link Text</label>
                                <input type="text" name="link_text" class="ss-input" placeholder="Learn More">
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-check"><input type="checkbox" name="is_active" value="1" checked> <span>Active (visible on homepage)</span></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Save Content</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== EDIT CONTENT MODALS (one per item) ==================== -->
<?php foreach ($allContent as $item): ?>
<div class="modal fade" id="editModal_<?= (int)$item['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= URL::to('admin/homepage/' . (int)$item['id'] . '/update') ?>">
                <?= $csrfField ?? '' ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen text-primary"></i> Edit Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Section</label>
                                <select name="section" class="ss-select" required>
                                    <option value="hero" <?= ($item['section'] ?? '') === 'hero' ? 'selected' : '' ?>>⭐ Hero Section</option>
                                    <option value="announcement" <?= ($item['section'] ?? '') === 'announcement' ? 'selected' : '' ?>>📢 Announcement</option>
                                    <option value="video" <?= ($item['section'] ?? '') === 'video' ? 'selected' : '' ?>>▶️ YouTube Video</option>
                                    <option value="event" <?= ($item['section'] ?? '') === 'event' ? 'selected' : '' ?>>📅 Event</option>
                                    <option value="testimonial" <?= ($item['section'] ?? '') === 'testimonial' ? 'selected' : '' ?>>💬 Testimonial</option>
                                    <option value="custom" <?= ($item['section'] ?? '') === 'custom' ? 'selected' : '' ?>>🧩 Custom Content</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="ss-input" value="<?= (int)($item['sort_order'] ?? 0) ?>" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Title</label>
                        <input type="text" name="title" class="ss-input" value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Subtitle</label>
                        <input type="text" name="subtitle" class="ss-input" value="<?= htmlspecialchars($item['subtitle'] ?? '') ?>">
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-form-label">Body Text</label>
                        <textarea name="body" class="ss-textarea" rows="3"><?= htmlspecialchars($item['body'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">YouTube URL</label>
                                <input type="url" name="video_url" class="ss-input" value="<?= htmlspecialchars($item['video_url'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Image URL</label>
                                <input type="url" name="image_url" class="ss-input" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Link URL</label>
                                <input type="url" name="link_url" class="ss-input" value="<?= htmlspecialchars($item['link_url'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Link Text</label>
                                <input type="text" name="link_text" class="ss-input" value="<?= htmlspecialchars($item['link_text'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="ss-form-group">
                        <label class="ss-check"><input type="checkbox" name="is_active" value="1" <?= !empty($item['is_active']) ? 'checked' : '' ?>> <span>Active (visible on homepage)</span></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> Update Content</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Hidden input for section override when "Add" button is clicked from a section -->
<?php // Removed: <input type="hidden" id="addSection" value=""> — no longer needed
      // since the "Add" button now sets the select value directly. ?>

<script>
function editContent(id) {
    new bootstrap.Modal(document.getElementById('editModal_' + id)).show();
}

// When the modal opens, reset the select to a default (optional UX improvement)
document.getElementById('addContentModal')?.addEventListener('hidden.bs.modal', function() {
    // Reset the select to its default after closing
    const sel = document.getElementById('addSection_select');
    if (sel) sel.selectedIndex = 0;
    const title = this.querySelector('.modal-title');
    if (title) title.innerHTML = '<i class="fas fa-plus text-primary"></i> Add Homepage Content';
});
</script>
