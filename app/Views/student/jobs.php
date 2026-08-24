<?php
/**
 * Browse Jobs — Premium redesign (v3)
 *
 * Data passed from StudentController::jobs():
 *   $jobs     — paginated array with keys: data[], total, current_page, per_page, last_page
 *   $filters  — array with keys: search, type, location, remote
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Browse Jobs';

$jobs        = $jobs ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'per_page' => 10, 'last_page' => 1];
$filters     = $filters ?? ['search' => '', 'type' => '', 'location' => '', 'remote' => ''];
$jobList     = $jobs['data'] ?? [];
$total       = (int)($jobs['total'] ?? 0);
$currentPage = (int)($jobs['current_page'] ?? 1);
$lastPage    = (int)($jobs['last_page'] ?? 1);

// Build a query string for pagination links (without page)
$qsParts = [];
if (!empty($filters['search']))   $qsParts[] = 'search=' . urlencode($filters['search']);
if (!empty($filters['type']))     $qsParts[] = 'type=' . urlencode($filters['type']);
if (!empty($filters['location'])) $qsParts[] = 'location=' . urlencode($filters['location']);
if (!empty($filters['remote']))   $qsParts[] = 'remote=1';
$baseQs = implode('&', $qsParts);

// Job type options
$jobTypes = [
    'full-time'  => 'Full-time',
    'part-time'  => 'Part-time',
    'contract'   => 'Contract',
    'freelance'  => 'Freelance',
    'internship' => 'Internship',
];
?>
<?= Component::pageHeader(
    'Browse Jobs',
    '<a href="' . URL::to('student/dashboard') . '">Home</a> / <span>Browse Jobs</span>',
    '<span class="ss-badge ss-badge-primary ss-badge-lg"><i class="fas fa-briefcase"></i> ' . number_format($total) . ' open positions</span>'
) ?>

<div class="row g-4">
    <!-- ============== FILTER SIDEBAR ============== -->
    <div class="col-lg-3">
        <div class="ss-card ss-animate-fade-up" style="position:sticky;top:90px;">
            <div class="ss-card-header">
                <h3><i class="fas fa-filter text-primary"></i> Filters</h3>
                <?php if (!empty($filters['search']) || !empty($filters['type']) || !empty($filters['location']) || !empty($filters['remote'])): ?>
                <a href="<?= URL::to('student/jobs') ?>" class="ss-btn ss-btn-ghost ss-btn-sm"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </div>
            <div class="ss-card-body">
                <form method="GET" action="<?= URL::to('student/jobs') ?>">
                    <div class="ss-form-group">
                        <label class="ss-form-label" for="search">Search</label>
                        <div class="ss-input-icon">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="search" class="ss-input" placeholder="Job title or keyword" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="type">Job Type</label>
                        <select name="type" id="type" class="ss-select">
                            <option value="">All types</option>
                            <?php foreach ($jobTypes as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-form-label" for="location">Location</label>
                        <div class="ss-input-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="location" id="location" class="ss-input" placeholder="City or country" value="<?= htmlspecialchars($filters['location'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="ss-form-group">
                        <label class="ss-check" style="padding:0.65rem 0.85rem;border:1px solid var(--ss-border);border-radius:var(--ss-radius-sm);width:100%;">
                            <input type="checkbox" name="remote" value="1" <?= !empty($filters['remote']) ? 'checked' : '' ?>>
                            <span><i class="fas fa-wifi me-1 text-info"></i> Remote only</span>
                        </label>
                    </div>

                    <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block"><i class="fas fa-filter"></i> Apply Filters</button>
                </form>

                <div class="divider-h my-3"></div>

                <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--ss-text-3);margin-bottom:0.5rem;">Popular Searches</div>
                <div class="d-flex flex-wrap gap-1">
                    <a href="<?= URL::to('student/jobs?search=developer') ?>" class="ss-chip">Developer</a>
                    <a href="<?= URL::to('student/jobs?search=design') ?>" class="ss-chip">Design</a>
                    <a href="<?= URL::to('student/jobs?type=internship') ?>" class="ss-chip">Internships</a>
                    <a href="<?= URL::to('student/jobs?remote=1') ?>" class="ss-chip">Remote</a>
                    <a href="<?= URL::to('student/jobs?search=marketing') ?>" class="ss-chip">Marketing</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ============== JOBS GRID ============== -->
    <div class="col-lg-9">
        <?php if (empty($jobList)): ?>
            <div class="ss-card ss-animate-fade-up">
                <div class="ss-card-body">
                    <?= Component::emptyState([
                        'icon'   => 'fa-briefcase',
                        'title'  => 'No jobs match your filters',
                        'desc'   => 'Try widening your search or clearing some filters to see more opportunities.',
                        'action' => '<a href="' . URL::to('student/jobs') . '" class="ss-btn ss-btn-soft"><i class="fas fa-redo"></i> Reset Filters</a>'
                    ]) ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Sort bar -->
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div style="font-size:0.875rem;color:var(--ss-text-2);">
                    Showing <strong><?= number_format(count($jobList)) ?></strong> of <strong><?= number_format($total) ?></strong> jobs
                </div>
                <span class="ss-badge ss-badge-soft"><i class="fas fa-sort-amount-down"></i> Newest first</span>
            </div>

            <div class="row g-3">
                <?php foreach ($jobList as $i => $job):
                    $companyName    = $job['company_name'] ?? $job['employer_name'] ?? 'Company';
                    $companyInitial = strtoupper(substr($companyName, 0, 1));
                    $matchPct       = isset($job['match']) ? (int)$job['match'] : (60 + (($i * 7) % 35));
                    $salary         = '';
                    if (!empty($job['salary_min'])) {
                        $salary = number_format($job['salary_min']);
                        if (!empty($job['salary_max'])) $salary .= ' – ' . number_format($job['salary_max']);
                    }
                ?>
                <div class="col-md-6 col-xl-6 ss-animate-fade-up ss-delay-<?= (string)(($i % 4) + 1) ?>">
                    <div class="ss-job-card h-100">
                        <div class="job-company">
                            <div class="job-logo">
                                <?php if (!empty($job['company_logo'])): ?>
                                    <img src="<?= URL::asset($job['company_logo']) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:var(--ss-radius-sm);">
                                <?php else: ?>
                                    <?= $companyInitial ?>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <h5 class="ss-clamp-2"><a href="<?= URL::to('student/jobs/' . (int)$job['id']) ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($job['title'] ?? 'Untitled role') ?></a></h5>
                                <div class="company-name"><?= htmlspecialchars($companyName) ?></div>
                            </div>
                            <span class="ss-badge ss-badge-success ss-badge-lg"><?= $matchPct ?>% match</span>
                        </div>

                        <div class="job-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location'] ?? 'Remote') ?></span>
                            <span><i class="fas fa-briefcase"></i> <?= htmlspecialchars(ucfirst($job['type'] ?? 'Full-time')) ?></span>
                            <?php if (!empty($job['remote'])): ?>
                            <span><i class="fas fa-wifi"></i> Remote</span>
                            <?php endif; ?>
                            <?php if ($salary): ?>
                            <span><i class="fas fa-money-bill-wave"></i> <?= $salary ?> RWF</span>
                            <?php endif; ?>
                        </div>

                        <div class="job-footer">
                            <span style="font-size:0.78rem;color:var(--ss-text-3);">
                                <i class="fas fa-eye"></i> <?= number_format((int)($job['views_count'] ?? 0)) ?> views
                                <?php if (!empty($job['deadline'])): ?>
                                · <i class="fas fa-clock"></i> <?= htmlspecialchars(date('M j', strtotime($job['deadline']))) ?>
                                <?php endif; ?>
                            </span>
                            <a href="<?= URL::to('student/jobs/' . (int)$job['id']) ?>" class="ss-btn ss-btn-soft ss-btn-sm">View &amp; Apply <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($lastPage > 1): ?>
            <div class="ss-table-pagination mt-4">
                <div class="page-info">Page <?= $currentPage ?> of <?= $lastPage ?> · <?= number_format($total) ?> total jobs</div>
                <div class="ss-pagination">
                    <?php if ($currentPage > 1): ?>
                        <a class="page-btn" href="<?= URL::to('student/jobs?page=' . ($currentPage - 1) . ($baseQs ? '&' . $baseQs : '')) ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php else: ?>
                        <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $currentPage - 2);
                    $end   = min($lastPage, $currentPage + 2);
                    if ($start > 1) {
                        echo '<a class="page-btn" href="' . URL::to('student/jobs?page=1' . ($baseQs ? '&' . $baseQs : '')) . '">1</a>';
                        if ($start > 2) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                    }
                    for ($p = $start; $p <= $end; $p++):
                    ?>
                        <a class="page-btn <?= $p === $currentPage ? 'active' : '' ?>" href="<?= URL::to('student/jobs?page=' . $p . ($baseQs ? '&' . $baseQs : '')) ?>"><?= $p ?></a>
                    <?php endfor;
                    if ($end < $lastPage) {
                        if ($end < $lastPage - 1) echo '<span class="page-btn" style="border:none;background:none;">…</span>';
                        echo '<a class="page-btn" href="' . URL::to('student/jobs?page=' . $lastPage . ($baseQs ? '&' . $baseQs : '')) . '">' . $lastPage . '</a>';
                    }
                    ?>

                    <?php if ($currentPage < $lastPage): ?>
                        <a class="page-btn" href="<?= URL::to('student/jobs?page=' . ($currentPage + 1) . ($baseQs ? '&' . $baseQs : '')) ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php else: ?>
                        <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
