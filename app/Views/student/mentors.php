<?php
/**
 * Mentors — Browse and book mentorship sessions (v3)
 *
 * Data passed from InnovationController::mentors():
 *   $mentors (array with first_name, last_name, avatar, specialization, title, company,
 *             years_experience, hourly_rate, rating, total_sessions, availability, bio)
 *
 * Form posts to student/mentors/{id}/book with fields:
 *   topic, description, scheduled_at, duration_minutes
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Find a Mentor';

$mentors = $mentors ?? [];
?>
<?= Component::pageHeader(
    'Find a Mentor',
    '<a href="' . URL::to('student/dashboard') . '">Dashboard</a> / <span>Mentors</span>',
    '<div class="ss-topbar-search" style="width:240px;">' .
        '<i class="fas fa-search"></i>' .
        '<input type="text" placeholder="Search mentors..." data-live-search="#mentorsList" data-live-search-item=".mentor-card">' .
    '</div>'
) ?>

<div class="ss-card mb-4 ss-card-gradient ss-animate-fade-up">
    <div class="ss-card-body d-flex flex-wrap align-items-center gap-3">
        <div style="flex:1;min-width:240px;">
            <div style="font-size:0.85rem;opacity:0.9;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;">Get 1-on-1 Career Guidance</div>
            <h3 style="color:#fff;margin:0.25rem 0;font-size:1.3rem;">Connect with industry experts</h3>
            <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">Book sessions with experienced mentors from top companies. Get personalized advice on your career, resume, and skills.</p>
        </div>
        <div class="text-center">
            <div style="font-size:2.5rem;font-weight:900;color:#fff;line-height:1;"><?= count($mentors) ?></div>
            <div style="font-size:0.8rem;opacity:0.85;">Available Mentors</div>
        </div>
    </div>
</div>

<div id="mentorsList">
    <?php if (!empty($mentors)): ?>
        <div class="row g-4">
            <?php foreach ($mentors as $i => $m):
                $rating = (float)($m['rating'] ?? 0);
            ?>
                <div class="col-md-6 col-lg-4 mentor-card ss-animate-fade-up ss-delay-<?= (string)(($i % 5) + 1) ?>">
                    <div class="ss-card ss-hover-lift h-100" style="padding:1.5rem;">
                        <div class="text-center mb-3">
                            <div class="ss-avatar ss-avatar-xl mx-auto mb-2" style="background:var(--ss-gradient-cool);"><?= strtoupper(substr($m['first_name'] ?? 'M', 0, 1)) ?></div>
                            <h5 style="font-size:1rem;margin-bottom:0.25rem;"><?= htmlspecialchars(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')) ?></h5>
                            <div style="font-size:0.82rem;color:var(--ss-text-3);"><?= htmlspecialchars($m['title'] ?? 'Mentor') ?></div>
                            <div style="font-size:0.78rem;color:var(--ss-primary);font-weight:600;"><?= htmlspecialchars($m['specialization'] ?? '') ?></div>
                        </div>

                        <div class="d-flex justify-content-around mb-3 py-2" style="background:var(--ss-surface-2);border-radius:var(--ss-radius);">
                            <div class="text-center">
                                <div style="font-weight:800;color:var(--ss-warning);font-size:1.1rem;">
                                    <?= str_repeat('★', (int)$rating) ?><?= str_repeat('☆', 5 - (int)$rating) ?>
                                </div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);"><?= number_format($rating, 1) ?> rating</div>
                            </div>
                            <div class="text-center">
                                <div style="font-weight:800;color:var(--ss-primary);font-size:1.1rem;"><?= (int)($m['total_sessions'] ?? 0) ?></div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Sessions</div>
                            </div>
                            <div class="text-center">
                                <div style="font-weight:800;color:var(--ss-success);font-size:1.1rem;"><?= (int)($m['years_experience'] ?? 0) ?>y</div>
                                <div style="font-size:0.7rem;color:var(--ss-text-3);">Experience</div>
                            </div>
                        </div>

                        <?php if (!empty($m['bio'])): ?>
                            <p style="font-size:0.82rem;color:var(--ss-text-2);margin-bottom:0.75rem;" class="ss-clamp-3"><?= htmlspecialchars($m['bio']) ?></p>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:0.82rem;">
                            <span style="color:var(--ss-text-3);"><i class="fas fa-building me-1"></i> <?= htmlspecialchars($m['company'] ?? 'Independent') ?></span>
                            <?php if (!empty($m['hourly_rate']) && $m['hourly_rate'] > 0): ?>
                                <?= Component::badge('$' . htmlspecialchars($m['hourly_rate']) . '/hr', 'success') ?>
                            <?php else: ?>
                                <?= Component::badge('Free', 'primary') ?>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="ss-btn ss-btn-gradient flex-fill" data-bs-toggle="modal" data-bs-target="#bookModal<?= (int)$m['id'] ?>">
                                <i class="fas fa-calendar-plus"></i> Book Session
                            </button>
                        </div>
                        <?php if (($m['availability'] ?? '') === 'available'): ?>
                            <div class="text-center mt-2" style="font-size:0.72rem;color:var(--ss-success);"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--ss-success);"></span> Available now</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Book Modal -->
                <div class="modal fade" id="bookModal<?= (int)$m['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="<?= URL::to('student/mentors/' . (int)$m['id'] . '/book') ?>">
                                <?= $csrfField ?? '' ?>
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-calendar-plus text-primary me-2"></i> Book Session with <?= htmlspecialchars($m['first_name'] ?? 'Mentor') ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="ss-form-group mb-3">
                                        <label class="ss-form-label">Topic <span class="req">*</span></label>
                                        <input type="text" name="topic" class="ss-input" required placeholder="e.g. Resume review, career advice">
                                    </div>
                                    <div class="ss-form-group mb-3">
                                        <label class="ss-form-label">Description</label>
                                        <textarea name="description" class="ss-textarea" placeholder="What would you like to discuss?"></textarea>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="ss-form-group">
                                                <label class="ss-form-label">Date &amp; Time <span class="req">*</span></label>
                                                <input type="datetime-local" name="scheduled_at" class="ss-input" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ss-form-group">
                                                <label class="ss-form-label">Duration</label>
                                                <select name="duration_minutes" class="ss-select">
                                                    <option value="30">30 minutes</option>
                                                    <option value="60" selected>1 hour</option>
                                                    <option value="90">1.5 hours</option>
                                                    <option value="120">2 hours</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="ss-btn ss-btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="ss-btn ss-btn-gradient"><i class="fas fa-paper-plane"></i> Request Session</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="ss-card">
            <div class="ss-card-body">
                <?= Component::emptyState(['icon' => 'fa-chalkboard-teacher', 'title' => 'No mentors available', 'desc' => 'Mentor profiles will appear here once they join the platform.']) ?>
            </div>
        </div>
    <?php endif; ?>
</div>
