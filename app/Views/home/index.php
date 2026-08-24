<?php
/**
 * Homepage — Premium landing page
 */
use App\Helpers\URL;
use App\Helpers\Component;

$jobs = $featuredJobs['data'] ?? [];
$internships = $featuredInternships['data'] ?? [];
$hc = $homepageContent ?? ['hero' => null, 'announcements' => [], 'videos' => [], 'events' => [], 'testimonials' => []];
?>

<!-- ==================== HERO ==================== -->
<section class="ss-hero">
    <div class="ss-hero-blob ss-hero-blob-1"></div>
    <div class="ss-hero-blob ss-hero-blob-2"></div>
    <div class="container hero-content">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="ss-badge ss-badge-primary ss-badge-lg mb-3 ss-animate-fade-down d-inline-flex">
                    <i class="fas fa-bolt"></i> Trusted by 5,000+ students & 200+ employers
                </span>
                <h1 class="ss-animate-fade-up mb-3">Launch your career with <span class="text-gradient">SkillSystem</span></h1>
                <p class="lead ss-animate-fade-up ss-delay-1 mb-4">
                    The all-in-one platform connecting student talent with real-world opportunities.
                    Build your portfolio, get AI-powered career recommendations, and land your dream job.
                </p>
                <div class="d-flex justify-content-center justify-content-lg-start mb-3">
                    <div class="ss-hero-search ss-animate-fade-up ss-delay-2">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-field" placeholder="Job title, skill, or company" id="heroSearch">
                        <div class="search-divider d-none d-md-block"></div>
                        <input type="text" class="search-field d-none d-md-block" placeholder="Location" id="heroLocation">
                        <button class="ss-btn ss-btn-gradient" onclick="window.location.href='<?= URL::to('login') ?>'">
                            <i class="fas fa-search d-md-none"></i><span class="d-none d-md-inline">Search</span>
                        </button>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start ss-animate-fade-up ss-delay-3">
                    <span class="text-muted-2" style="font-size:0.82rem;line-height:2;">Popular:</span>
                    <?php foreach (['Software Engineering', 'Data Analyst', 'Marketing', 'Internship'] as $tag): ?>
                        <a href="<?= URL::to('login') ?>" class="ss-chip"><?= $tag ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex gap-2 justify-content-center justify-content-lg-start mt-4 ss-animate-fade-up ss-delay-4">
                    <a href="<?= URL::to('register') ?>" class="ss-btn ss-btn-gradient ss-btn-lg"><i class="fas fa-rocket"></i> Get Started Free</a>
                    <a href="<?= URL::to('login') ?>" class="ss-btn ss-btn-light ss-btn-lg"><i class="fas fa-sign-in-alt"></i> Sign In</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="ss-animate-fade-up ss-delay-2" style="position:relative;max-width:400px;margin-left:auto;">
                    <div class="ss-card" style="padding:1.75rem;box-shadow:0 25px 60px -20px rgba(37,99,235,0.25), 0 10px 20px -10px rgba(15,23,42,0.1);border:none;">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="ss-avatar ss-avatar-lg" style="background:var(--ss-grad-warm);flex-shrink:0;">J</div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;font-size:1rem;color:var(--ss-text);">Jean Pierre H.</div>
                                <div style="font-size:0.8rem;color:var(--ss-text-3);">Software Engineering · UR</div>
                            </div>
                            <span class="ss-badge ss-badge-success" style="flex-shrink:0;"><i class="fas fa-check"></i> Hired</span>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span style="font-size:0.82rem;font-weight:600;color:var(--ss-text-2);">AI Resume Score</span>
                                <span style="font-size:0.95rem;font-weight:800;color:var(--ss-success);">94<span style="font-size:0.72rem;color:var(--ss-text-3);">/100</span></span>
                            </div>
                            <?= Component::progress(94, 'success', 'sm') ?>
                        </div>
                        <div style="font-size:0.72rem;font-weight:700;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Top Skills</div>
                        <div class="d-flex gap-1 flex-wrap mb-4">
                            <span class="ss-chip" style="font-size:0.75rem;">PHP</span>
                            <span class="ss-chip" style="font-size:0.75rem;">Laravel</span>
                            <span class="ss-chip" style="font-size:0.75rem;">MySQL</span>
                            <span class="ss-chip" style="font-size:0.75rem;">React</span>
                        </div>
                        <div style="background:linear-gradient(135deg, var(--ss-primary-light) 0%, rgba(var(--ss-accent-rgb),0.08) 100%);border-radius:var(--ss-r);padding:1rem;border:1px solid rgba(var(--ss-primary-rgb),0.15);">
                            <div style="font-size:0.68rem;color:var(--ss-primary);text-transform:uppercase;letter-spacing:0.06em;font-weight:700;margin-bottom:4px;"><i class="fas fa-briefcase"></i> Recent Offer</div>
                            <div style="font-size:0.92rem;font-weight:700;color:var(--ss-text);line-height:1.3;">Junior Developer</div>
                            <div style="font-size:0.78rem;color:var(--ss-text-2);margin-top:2px;">Bank of Kigali · Kigali, Rwanda</div>
                        </div>
                    </div>
                    <div style="position:absolute;bottom:-20px;left:-20px;background:var(--ss-surface);border:1px solid var(--ss-border);border-radius:var(--ss-r-lg);padding:0.85rem 1.1rem;box-shadow:var(--ss-shadow-lg);display:flex;align-items:center;gap:0.75rem;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--ss-grad-success);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;"><i class="fas fa-trophy"></i></div>
                        <div>
                            <div style="font-size:0.68rem;color:var(--ss-text-3);text-transform:uppercase;letter-spacing:0.05em;font-weight:700;">Profile</div>
                            <div style="font-size:1.1rem;font-weight:900;color:var(--ss-text);line-height:1;">100%</div>
                        </div>
                    </div>
                    <div style="position:absolute;top:-16px;right:-12px;background:var(--ss-grad-warm);color:#fff;border-radius:var(--ss-r);padding:0.5rem 0.85rem;box-shadow:0 10px 25px -8px rgba(245,158,11,0.5);font-size:0.72rem;font-weight:700;display:flex;align-items:center;gap:5px;">
                        <i class="fas fa-star"></i> Top Talent
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-5 pt-4 ss-animate-fade-up ss-delay-4" style="border-top:1px solid var(--ss-border);">
            <div class="col-6 col-md-3"><div class="ss-stat-block"><div class="stat-num"><span data-count="<?= (int)($stats['students'] ?? 1250) ?>">0</span>+</div><div class="stat-label">Active Students</div></div></div>
            <div class="col-6 col-md-3"><div class="ss-stat-block"><div class="stat-num"><span data-count="<?= (int)($stats['employers'] ?? 200) ?>">0</span>+</div><div class="stat-label">Employer Partners</div></div></div>
            <div class="col-6 col-md-3"><div class="ss-stat-block"><div class="stat-num"><span data-count="<?= (int)($stats['jobs'] ?? 800) ?>">0</span>+</div><div class="stat-label">Open Jobs</div></div></div>
            <div class="col-6 col-md-3"><div class="ss-stat-block"><div class="stat-num"><span data-count="<?= (int)($stats['internships'] ?? 320) ?>">0</span>+</div><div class="stat-label">Internships</div></div></div>
        </div>
    </div>
</section>

<!-- ==================== ANNOUNCEMENTS (from admin) ==================== -->
<?php if (!empty($hc['announcements'])): ?>
<section class="ss-section" style="background:var(--ss-surface-2);padding:2rem 0;">
    <div class="container">
        <div class="ss-section-header" style="margin-bottom:1.5rem;">
            <div class="eyebrow ss-reveal">📢 Latest Updates</div>
            <h2 class="ss-reveal" style="font-size:1.75rem;">Announcements</h2>
        </div>
        <div class="row g-3">
            <?php foreach ($hc['announcements'] as $ann): ?>
            <div class="col-md-6">
                <div class="ss-card ss-card-hover" style="padding:1.5rem;border-left:4px solid var(--ss-warning);">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:44px;height:44px;border-radius:var(--ss-r-sm);background:var(--ss-warning-light);color:var(--ss-warning);display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <h5 style="font-size:0.95rem;font-weight:700;"><?= htmlspecialchars($ann['title'] ?? '') ?></h5>
                            <?php if (!empty($ann['subtitle'])): ?>
                                <p style="font-size:0.82rem;color:var(--ss-text-2);margin:4px 0;"><?= htmlspecialchars($ann['subtitle']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($ann['body'])): ?>
                                <p style="font-size:0.78rem;color:var(--ss-text-3);margin:4px 0;"><?= htmlspecialchars($ann['body']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($ann['link_url'])): ?>
                                <a href="<?= htmlspecialchars($ann['link_url']) ?>" class="ss-btn ss-btn-soft ss-btn-sm mt-2"><?= htmlspecialchars($ann['link_text'] ?: 'Learn More') ?> <i class="fas fa-arrow-right"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== YOUTUBE VIDEOS (from admin) ==================== -->
<?php if (!empty($hc['videos'])): ?>
<section class="ss-section">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">▶️ Watch</div>
            <h2 class="ss-reveal">Featured Videos</h2>
            <p class="ss-reveal">Watch how SkillSystem helps students and employers connect.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($hc['videos'] as $vid):
                $vidUrl = $vid['video_url'] ?? '';
                $vidId = '';
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $vidUrl, $m)) {
                    $vidId = $m[1];
                }
            ?>
            <div class="col-md-6">
                <div class="ss-card ss-card-hover h-100" style="overflow:hidden;padding:0;">
                    <?php if ($vidId): ?>
                    <div style="position:relative;padding-top:56.25%;background:#000;">
                        <iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($vidId) ?>"
                            title="<?= htmlspecialchars($vid['title'] ?? 'Video') ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                    <?php else: ?>
                    <div style="padding-top:56.25%;background:var(--ss-surface-2);position:relative;">
                        <a href="<?= htmlspecialchars($vidUrl) ?>" target="_blank" style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--ss-danger);font-size:2rem;">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div style="padding:1.25rem;">
                        <h5 style="font-size:0.95rem;"><?= htmlspecialchars($vid['title'] ?? '') ?></h5>
                        <?php if (!empty($vid['subtitle'])): ?>
                            <p style="font-size:0.82rem;color:var(--ss-text-2);margin-top:4px;"><?= htmlspecialchars($vid['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== EVENTS (from admin) ==================== -->
<?php if (!empty($hc['events'])): ?>
<section class="ss-section" style="background:var(--ss-surface-2);" id="events">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">📅 Upcoming</div>
            <h2 class="ss-reveal">Events & Workshops</h2>
            <p class="ss-reveal">Join us at career fairs, workshops, and webinars.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($hc['events'] as $evt): ?>
            <div class="col-md-6 col-lg-4 ss-reveal">
                <div class="ss-card ss-card-hover h-100" style="padding:1.5rem;border-top:4px solid var(--ss-info);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:44px;height:44px;border-radius:var(--ss-r-sm);background:var(--ss-info-light);color:var(--ss-info);display:inline-flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h5 style="font-size:0.95rem;margin:0;"><?= htmlspecialchars($evt['title'] ?? '') ?></h5>
                            <?php if (!empty($evt['subtitle'])): ?>
                                <div style="font-size:0.75rem;color:var(--ss-text-3);"><?= htmlspecialchars($evt['subtitle']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($evt['body'])): ?>
                        <p style="font-size:0.82rem;color:var(--ss-text-2);margin:8px 0;"><?= htmlspecialchars($evt['body']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($evt['link_url'])): ?>
                        <a href="<?= htmlspecialchars($evt['link_url']) ?>" class="ss-btn ss-btn-soft ss-btn-sm mt-2"><?= htmlspecialchars($evt['link_text'] ?: 'Register') ?> <i class="fas fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== FEATURED JOBS ==================== -->
<section class="ss-section" id="jobs">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">Opportunities</div>
            <h2 class="ss-reveal">Featured Jobs</h2>
            <p class="ss-reveal">Hand-picked opportunities from top employers across Rwanda and beyond.</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($jobs)): ?>
                <?php foreach (array_slice($jobs, 0, 6) as $i => $job): ?>
                    <div class="col-md-6 col-lg-4 ss-reveal ss-delay-<?= ($i % 5) + 1 ?>">
                        <div class="ss-job-card">
                            <div class="job-company">
                                <div class="job-logo"><?= strtoupper(substr($job['company_name'] ?? $job['employer_name'] ?? 'C', 0, 1)) ?></div>
                                <div style="flex:1;min-width:0;">
                                    <h5 class="ss-clamp-2"><?= htmlspecialchars($job['title']) ?></h5>
                                    <div class="company-name"><?= htmlspecialchars($job['company_name'] ?? $job['employer_name'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="job-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location'] ?? 'Remote') ?></span>
                                <span><i class="fas fa-briefcase"></i> <?= htmlspecialchars(ucfirst($job['type'] ?? 'Full-time')) ?></span>
                                <?php if (!empty($job['salary_min'])): ?>
                                    <span><i class="fas fa-money-bill-wave"></i> <?= htmlspecialchars($job['salary_currency'] ?? 'RWF') ?> <?= number_format($job['salary_min']) ?>+</span>
                                <?php endif; ?>
                            </div>
                            <div class="job-footer">
                                <span class="ss-badge ss-badge-soft"><?= htmlspecialchars(ucfirst($job['type'] ?? 'Full-time')) ?></span>
                                <a href="<?= URL::to('login') ?>" class="ss-btn ss-btn-soft ss-btn-sm">View <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><?= Component::emptyState(['icon' => 'fa-briefcase', 'title' => 'No jobs available yet', 'desc' => 'Check back soon — employers are posting new opportunities every day.']) ?></div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= URL::to('login') ?>" class="ss-btn ss-btn-outline ss-btn-lg">View all jobs <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ==================== FEATURED INTERNSHIPS ==================== -->
<section class="ss-section" id="internships" style="background:var(--ss-surface-2);">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">Internships</div>
            <h2 class="ss-reveal">Featured Internships</h2>
            <p class="ss-reveal">Kick-start your career with hands-on experience at leading companies.</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($internships)): ?>
                <?php foreach (array_slice($internships, 0, 3) as $i => $intern): ?>
                    <div class="col-md-4 ss-reveal ss-delay-<?= $i + 1 ?>">
                        <div class="ss-card ss-card-hover h-100" style="padding:1.5rem;border-top:3px solid var(--ss-primary);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="ss-avatar ss-avatar-md" style="background:var(--ss-grad-cool);"><?= strtoupper(substr($intern['title'] ?? 'I', 0, 1)) ?></div>
                                <div>
                                    <h5 class="ss-clamp-2" style="font-size:0.95rem;margin-bottom:0;"><?= htmlspecialchars($intern['title']) ?></h5>
                                    <div style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars($intern['company_name'] ?? '') ?></div>
                                </div>
                            </div>
                            <p style="font-size:0.82rem;color:var(--ss-text-2);margin-bottom:0.75rem;"><?= htmlspecialchars(substr($intern['description'] ?? '', 0, 100)) ?>...</p>
                            <div class="d-flex flex-wrap gap-2 mb-2" style="font-size:0.78rem;color:var(--ss-text-3);">
                                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($intern['duration'] ?? '') ?> <?= htmlspecialchars($intern['duration_unit'] ?? '') ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($intern['location'] ?? 'Remote') ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top:1px solid var(--ss-border);">
                                <span class="ss-badge ss-badge-success"><i class="fas fa-users"></i> <?= (int)($intern['positions_available'] ?? 1) ?> positions</span>
                                <a href="<?= URL::to('login') ?>" class="ss-btn ss-btn-soft ss-btn-sm">Apply <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><?= Component::emptyState(['icon' => 'fa-user-graduate', 'title' => 'No internships yet', 'desc' => 'New internship opportunities are posted every week.']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==================== TOP COMPANIES ==================== -->
<section class="ss-section" id="companies">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">Partners</div>
            <h2 class="ss-reveal">Top Companies</h2>
            <p class="ss-reveal">Leading employers trust SkillSystem to find their next great hire.</p>
        </div>
        <div class="row g-3">
            <?php if (!empty($companies)): ?>
                <?php foreach ($companies as $i => $co): ?>
                    <div class="col-6 col-md-4 col-lg-2 ss-reveal ss-delay-<?= ($i % 5) + 1 ?>">
                        <div class="ss-logo-card">
                            <div class="logo-img"><?= strtoupper(substr($co['name'] ?? 'C', 0, 1)) ?></div>
                            <h5 style="font-size:0.88rem;"><?= htmlspecialchars($co['name'] ?? '') ?></h5>
                            <div class="subtitle" style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars($co['industry'] ?? '') ?></div>
                            <?php if (!empty($co['verified'])): ?>
                                <span class="ss-badge ss-badge-success mt-2"><i class="fas fa-check"></i> Verified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted-2">No company profiles yet.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==================== TOP UNIVERSITIES ==================== -->
<section class="ss-section" id="universities" style="background:var(--ss-surface-2);">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">Education Partners</div>
            <h2 class="ss-reveal">Top Universities</h2>
            <p class="ss-reveal">Premier institutions empowering their students with SkillSystem.</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($universities)): ?>
                <?php foreach (array_slice($universities, 0, 4) as $i => $uni): ?>
                    <div class="col-md-6 col-lg-3 ss-reveal ss-delay-<?= $i + 1 ?>">
                        <div class="ss-card ss-card-hover h-100" style="padding:1.5rem;text-align:center;">
                            <div class="ss-avatar ss-avatar-xl mx-auto mb-3" style="background:var(--ss-grad-warm);"><i class="fas fa-university"></i></div>
                            <h5 style="font-size:0.95rem;margin-bottom:0.25rem;"><?= htmlspecialchars($uni['uni_name'] ?? '') ?></h5>
                            <div style="font-size:0.78rem;color:var(--ss-text-3);margin-bottom:0.5rem;"><?= htmlspecialchars($uni['location'] ?? '') ?></div>
                            <div class="d-flex justify-content-around pt-3 mt-2" style="border-top:1px solid var(--ss-border);">
                                <div><div style="font-weight:800;color:var(--ss-primary);font-size:1.1rem;"><?= number_format($uni['total_students'] ?? 0) ?></div><div style="font-size:0.7rem;color:var(--ss-text-3);">Students</div></div>
                                <div><div style="font-weight:800;color:var(--ss-success);font-size:1.1rem;"><i class="fas fa-check-circle"></i></div><div style="font-size:0.7rem;color:var(--ss-text-3);">Partner</div></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted-2">No university partners yet.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==================== FEATURED STUDENTS ==================== -->
<?php if (!empty($students)): ?>
<section class="ss-section">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">Student Talent</div>
            <h2 class="ss-reveal">Featured Students</h2>
            <p class="ss-reveal">Meet top-performing students with complete profiles and impressive portfolios.</p>
        </div>
        <div class="row g-4">
            <?php foreach (array_slice($students, 0, 4) as $i => $s): ?>
                <div class="col-md-6 col-lg-3 ss-reveal ss-delay-<?= $i + 1 ?>">
                    <div class="ss-card ss-card-hover h-100" style="padding:1.5rem;text-align:center;">
                        <div class="ss-avatar ss-avatar-xl mx-auto mb-3"><?= strtoupper(substr($s['first_name'] ?? 'S', 0, 1)) ?></div>
                        <h5 style="font-size:0.95rem;"><?= htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?></h5>
                        <div style="font-size:0.78rem;color:var(--ss-text-3);margin-bottom:0.5rem;"><?= htmlspecialchars($s['department'] ?? '') ?></div>
                        <?php if (!empty($s['bio'])): ?>
                            <p style="font-size:0.82rem;color:var(--ss-text-2);margin-bottom:1rem;" class="ss-clamp-2"><?= htmlspecialchars($s['bio']) ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center pt-3" style="border-top:1px solid var(--ss-border);">
                            <div class="text-start"><div style="font-size:0.7rem;color:var(--ss-text-3);">Profile</div><div style="font-weight:800;color:var(--ss-primary);"><?= (int)$s['profile_completion'] ?>%</div></div>
                            <span class="ss-badge ss-badge-success"><i class="fas fa-star"></i> Top Talent</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== CAREER RESOURCES / FEATURES ==================== -->
<section class="ss-section" id="features" style="background:#0F172A;color:#fff;">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal" style="color:#60A5FA;">Career Tools</div>
            <h2 class="ss-reveal" style="color:#fff;">Everything you need to succeed</h2>
            <p class="ss-reveal" style="color:rgba(255,255,255,0.7);">Powerful tools built for students, employers, and universities — all in one platform.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($resources as $i => $r): ?>
                <div class="col-md-6 col-lg-4 ss-reveal ss-delay-<?= ($i % 5) + 1 ?>">
                    <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:var(--ss-r-lg);padding:1.75rem;height:100%;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.transform='translateY(-4px)'" onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.transform='none'">
                        <div style="width:56px;height:56px;border-radius:var(--ss-r);background:rgba(255,255,255,0.1);display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;margin-bottom:1.1rem;"><i class="fas <?= $r['icon'] ?>"></i></div>
                        <h4 style="color:#fff;font-size:1.1rem;margin-bottom:0.5rem;"><?= htmlspecialchars($r['title']) ?></h4>
                        <p style="color:rgba(255,255,255,0.7);font-size:0.875rem;margin:0;"><?= htmlspecialchars($r['desc']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== TESTIMONIALS ==================== -->
<section class="ss-section">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">Testimonials</div>
            <h2 class="ss-reveal">Loved by students & employers</h2>
            <p class="ss-reveal">Real stories from real people who found success with SkillSystem.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $i => $t): ?>
                <div class="col-md-6 col-lg-4 ss-reveal ss-delay-<?= ($i % 5) + 1 ?>">
                    <div class="ss-testimonial-card">
                        <div class="quote-mark">"</div>
                        <div class="stars">
                            <?php for ($j = 0; $j < 5; $j++): ?><i class="fas fa-star<?= $j < $t['rating'] ? '' : '-o' ?>"></i><?php endfor; ?>
                        </div>
                        <p class="testimonial-body">"<?= htmlspecialchars($t['text']) ?>"</p>
                        <div class="testimonial-author">
                            <div class="ss-avatar ss-avatar-md"><?= htmlspecialchars($t['avatar']) ?></div>
                            <div>
                                <div style="font-weight:700;font-size:0.9rem;"><?= htmlspecialchars($t['name']) ?></div>
                                <div style="font-size:0.78rem;color:var(--ss-text-3);"><?= htmlspecialchars($t['role']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== COMMUNITY MEMBERS ==================== -->
<?php if (!empty($members)): ?>
<section class="ss-section" id="community">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">👥 Our Community</div>
            <h2 class="ss-reveal">Community Members <span class="ss-badge ss-badge-primary ss-badge-lg"><?= (int)($memberCount ?? count($members)) ?></span></h2>
            <p class="ss-reveal">Meet all the students, employers, mentors, and universities in our network.</p>
        </div>
        <div class="row g-3" id="communityGrid">
            <?php foreach ($members as $i => $m):
                $mName = trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')) ?: 'Member';
                $mInitial = strtoupper(substr($mName, 0, 1));
                $mRole = $m['role_slug'] ?? $m['role_name'] ?? 'member';
                $mAvatarPath = $m['avatar'] ?? '';
                $mAvatarUrl = (!empty($mAvatarPath)) ? URL::asset($mAvatarPath) : '';
                $mAvatarExists = (!empty($mAvatarPath) && file_exists(ROOT_PATH . '/public/assets/' . $mAvatarPath));
                $roleColors = [
                    'student' => 'success', 'employer' => 'primary', 'university' => 'warning',
                    'mentor' => 'info', 'admin' => 'danger',
                ];
                $roleColor = $roleColors[$mRole] ?? 'accent';
                // Hide cards after the 12th initially (CSS class hides them until "Show All" is clicked)
                $hiddenClass = ($i >= 12) ? 'community-hidden' : '';
            ?>
            <div class="col-md-6 col-lg-4 community-card <?= $hiddenClass ?>">
                <div class="ss-card ss-card-hover" style="padding:1.25rem;">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Avatar (photo or initial) -->
                        <?php if ($mAvatarExists): ?>
                            <img src="<?= htmlspecialchars($mAvatarUrl) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--ss-border);flex-shrink:0;">
                        <?php else: ?>
                            <div style="width:56px;height:56px;border-radius:50%;background:var(--ss-<?= $roleColor ?>-light);color:var(--ss-<?= $roleColor ?>);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;flex-shrink:0;"><?= htmlspecialchars($mInitial) ?></div>
                        <?php endif; ?>

                        <!-- Name, role, email, phone -->
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:0.9rem;line-height:1.3;"><?= htmlspecialchars($mName) ?></div>
                            <div style="margin-top:2px;">
                                <span class="ss-badge ss-badge-<?= $roleColor ?>" style="font-size:0.68rem;text-transform:capitalize;"><?= htmlspecialchars(ucfirst($mRole)) ?></span>
                            </div>
                            <?php if (!empty($m['email'])): ?>
                                <div style="font-size:0.78rem;color:var(--ss-text-2);margin-top:6px;display:flex;align-items:center;gap:4px;">
                                    <i class="fas fa-envelope" style="font-size:0.7rem;color:var(--ss-text-3);"></i>
                                    <span class="text-truncate" style="max-width:200px;"><?= htmlspecialchars($m['email']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($m['phone'])): ?>
                                <div style="font-size:0.78rem;color:var(--ss-text-2);margin-top:2px;display:flex;align-items:center;gap:4px;">
                                    <i class="fas fa-phone" style="font-size:0.7rem;color:var(--ss-text-3);"></i>
                                    <span><?= htmlspecialchars($m['phone']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($members) > 12): ?>
        <div class="text-center mt-4">
            <button type="button" class="ss-btn ss-btn-light" id="showAllMembers">
                <i class="fas fa-users"></i> Show all <?= (int)$memberCount ?> members
            </button>
        </div>
        <script>
        (function() {
            const btn = document.getElementById('showAllMembers');
            if (!btn) return;
            btn.addEventListener('click', function() {
                document.querySelectorAll('.community-card.community-hidden').forEach(function(card) {
                    card.classList.remove('community-hidden');
                });
                btn.style.display = 'none';
            });
        })();
        </script>
        <style>.community-hidden { display: none; }</style>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="<?= URL::to('register') ?>" class="ss-btn ss-btn-gradient"><i class="fas fa-user-plus"></i> Join our community</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== FAQ ==================== -->
<section class="ss-section" id="faq" style="background:var(--ss-surface-2);">
    <div class="container">
        <div class="ss-section-header">
            <div class="eyebrow ss-reveal">FAQ</div>
            <h2 class="ss-reveal">Frequently asked questions</h2>
            <p class="ss-reveal">Everything you need to know about SkillSystem. Can't find an answer? <a href="#">Contact us</a>.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php foreach ($faqs as $f): ?>
                    <div class="ss-faq-item">
                        <div class="ss-faq-header"><span><?= htmlspecialchars($f['q']) ?></span><i class="fas fa-chevron-down"></i></div>
                        <div class="ss-faq-body"><?= htmlspecialchars($f['a']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ==================== NEWSLETTER ==================== -->
<section class="ss-section">
    <div class="container">
        <div class="ss-newsletter">
            <span class="ss-badge" style="background:rgba(255,255,255,0.2);color:#fff;margin-bottom:1rem;"><i class="fas fa-envelope"></i> Newsletter</span>
            <h2>Get career tips & job alerts</h2>
            <p>Join 5,000+ students receiving weekly career advice, job opportunities, and platform updates.</p>
            <form onsubmit="event.preventDefault(); window.ssToast && ssToast.show('Subscribed successfully! Check your inbox.', 'success');">
                <input type="email" placeholder="your@email.com" required>
                <button type="submit" class="ss-btn ss-btn-light ss-btn-lg">Subscribe <i class="fas fa-paper-plane"></i></button>
            </form>
            <div style="font-size:0.78rem;opacity:0.7;margin-top:1rem;">We respect your privacy. Unsubscribe anytime.</div>
        </div>
    </div>
</section>
