<?php
/**
 * Portfolio — Premium redesign (v3)
 *
 * Data passed from StudentController::portfolio():
 *   $student, $portfolios
 *
 * Add form posts to student/portfolio/add with fields:
 *   title, description, url, technologies, image (file)
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'My Portfolio';

$student    = $student ?? [];
$portfolios = $portfolios ?? [];
?>
<?= Component::pageHeader(
    'My Portfolio',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Portfolio</span>',
    '<span class="ss-badge ss-badge-primary ss-badge-lg"><i class="fas fa-folder-plus"></i> ' . count($portfolios) . ' projects</span>' .
    '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="fas fa-plus"></i> <span class="d-none d-md-inline">Add Project</span></button>'
) ?>

<!-- ============== PORTFOLIO GALLERY ============== -->
<?php if (empty($portfolios)): ?>
<div class="ss-card ss-animate-fade-up">
    <div class="ss-card-body">
        <?= Component::emptyState([
            'icon'   => 'fa-folder-open',
            'title'  => 'No portfolio projects yet',
            'desc'   => "Showcase your best work — class projects, hackathon entries, side hustles or freelance gigs. A strong portfolio can set you apart from other applicants.",
            'action' => '<button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="fas fa-plus"></i> Add Your First Project</button>'
        ]) ?>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($portfolios as $i => $p):
        $techs    = array_filter(array_map('trim', explode(',', $p['technologies'] ?? '')));
        $hasImage = !empty($p['image']);
    ?>
    <div class="col-md-6 col-lg-4 ss-animate-fade-up ss-delay-<?= (string)(($i % 4) + 1) ?>">
        <div class="ss-card ss-hover-lift h-100" style="overflow:hidden;">
            <!-- Project image / placeholder -->
            <div style="position:relative;height:180px;overflow:hidden;background:var(--ss-gradient-soft);">
                <?php if ($hasImage): ?>
                    <img src="<?= URL::asset($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(79,70,229,0.08),rgba(6,182,212,0.08));">
                        <i class="fas fa-code" style="font-size:2.5rem;color:var(--ss-primary);opacity:0.3;"></i>
                    </div>
                <?php endif; ?>
                <!-- Quick action overlay -->
                <div style="position:absolute;top:0.75rem;right:0.75rem;display:flex;gap:4px;">
                    <?php if (!empty($p['url'])): ?>
                    <a href="<?= htmlspecialchars($p['url']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-icon" style="background:rgba(255,255,255,0.9);border:none;width:32px;height:32px;" title="Open project"><i class="fas fa-external-link-alt"></i></a>
                    <?php endif; ?>
                </div>
                <div style="position:absolute;bottom:0.75rem;left:0.75rem;">
                    <span class="ss-badge ss-badge-soft" style="background:rgba(255,255,255,0.9);"><i class="fas fa-calendar"></i> <?= htmlspecialchars(date('M Y', strtotime($p['created_at']))) ?></span>
                </div>
            </div>

            <!-- Body -->
            <div class="ss-card-body">
                <h5 class="ss-clamp-2" style="font-size:1rem;margin-bottom:0.4rem;"><?= htmlspecialchars($p['title']) ?></h5>
                <p class="ss-clamp-3" style="color:var(--ss-text-2);font-size:0.85rem;margin-bottom:0.75rem;"><?= htmlspecialchars($p['description'] ?? 'No description provided.') ?></p>

                <?php if (!empty($techs)): ?>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <?php foreach (array_slice($techs, 0, 5) as $tech): ?>
                        <span class="ss-chip ss-chip-primary" style="font-size:0.72rem;padding:3px 10px;"><?= htmlspecialchars($tech) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($techs) > 5): ?>
                        <span class="ss-chip" style="font-size:0.72rem;padding:3px 10px;">+<?= count($techs) - 5 ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="ss-card-footer d-flex align-items-center justify-content-between">
                <?php if (!empty($p['url'])): ?>
                    <a href="<?= htmlspecialchars($p['url']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-soft ss-btn-sm"><i class="fas fa-external-link-alt"></i> View Project</a>
                <?php else: ?>
                    <span style="font-size:0.78rem;color:var(--ss-text-3);"><i class="fas fa-link-slash"></i> No live URL</span>
                <?php endif; ?>
                <div class="d-flex gap-1">
                    <button class="ss-btn ss-btn-ghost ss-btn-sm" title="Edit"><i class="fas fa-pen"></i></button>
                    <form method="POST" action="<?= URL::to('student/portfolio/delete') ?>" onsubmit="return confirm('Delete this project? This cannot be undone.');" style="display:inline;">
                        <?= $csrfField ?? '' ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="ss-btn ss-btn-ghost ss-btn-sm" title="Delete" style="color:var(--ss-danger);"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tips card -->
<div class="ss-card ss-card-glass ss-animate-fade-up mt-4">
    <div class="ss-card-body">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h4 style="font-size:1.05rem;font-weight:700;margin-bottom:0.4rem;"><i class="fas fa-lightbulb text-warning me-1"></i> Make your portfolio stand out</h4>
                <p style="font-size:0.875rem;color:var(--ss-text-2);margin:0;">Add a screenshot, list the technologies you used, and include a live link or GitHub repo. Employers spend an average of <strong>43 seconds</strong> reviewing each portfolio.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="ss-btn ss-btn-gradient" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="fas fa-plus"></i> Add Project</button>
            </div>
        </div>
    </div>
</div>

<!-- ============== ADD PROJECT MODAL ============== -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProjectLabel"><i class="fas fa-folder-plus text-primary me-1"></i> Add Portfolio Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= URL::to('student/portfolio/add') ?>" enctype="multipart/form-data" data-validate>
                <?= $csrfField ?? '' ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <?= Component::floatField('title', 'Project title *', 'text', null, ['required' => true, 'id' => 'proj-title']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= Component::floatField('url', 'Live URL', 'url', null, ['id' => 'proj-url', 'attr' => 'data-validate="url"']) ?>
                        </div>
                        <div class="col-12">
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="proj-desc">Description <span style="color:var(--ss-text-3);font-weight:400;">— what does it do and what was your role?</span></label>
                                <textarea name="description" id="proj-desc" class="ss-textarea" rows="4" placeholder="e.g. A real-time chat application built with Socket.IO and React. I designed the backend API, set up authentication with JWT, and implemented the message persistence layer using PostgreSQL."></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="proj-tech">Technologies <span style="color:var(--ss-text-3);font-weight:400;">— comma separated</span></label>
                                <input type="text" name="technologies" id="proj-tech" class="ss-input" placeholder="React, Node.js, PostgreSQL, AWS S3" value="">
                                <div class="ss-form-hint">Press Enter after each technology. These will appear as filterable chips on your portfolio.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ss-form-group">
                                <label class="ss-form-label">Project screenshot</label>
                                <label class="ss-file-upload" style="cursor:pointer;">
                                    <input type="file" name="image" accept="image/png,image/jpeg,image/gif" data-file-preview="#proj-image-preview" style="display:none;">
                                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="upload-text">Click to browse or drag an image here</div>
                                    <div class="upload-hint">PNG, JPG or GIF · Max 5MB · Recommended 1200×675</div>
                                </label>
                                <div id="proj-image-preview" style="display:none;margin-top:0.75rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-plus"></i> Add Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
