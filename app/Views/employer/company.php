<?php
/**
 * Employer — Company Profile (premium redesign v3)
 *
 * Data passed from EmployerController::company():
 *   $employer — employer row with: id, company_name, industry, company_size,
 *               location, website, description, company_logo, founded_year
 *
 * Form submits to POST /employer/company/update (multipart/form-data for logo upload).
 * Controller reads: company_name, industry, company_size, location, website,
 *                   description, company_logo ($_FILES), founded_year
 */
use App\Helpers\URL;
use App\Helpers\Session;
use App\Helpers\Component;
use App\Helpers\Theme;

$pageTitle = 'Company Profile';

$employer     = $employer ?? [];
$companyName  = $employer['company_name'] ?? 'Your Company';
$industry     = $employer['industry'] ?? '';
$logo         = $employer['company_logo'] ?? '';
$initial      = strtoupper(substr($companyName, 0, 1));

$sizes = ['1-10', '11-50', '51-200', '200-500', '500-1000', '1000+'];
?>
<?= Component::pageHeader(
    'Company Profile',
    '<a href="' . URL::to('employer/dashboard') . '">Home</a> / <span>Company Profile</span>',
    '<a href="' . URL::to('employer/dashboard') . '" class="ss-btn ss-btn-light"><i class="fas fa-arrow-left"></i> <span class="d-none d-md-inline">Back</span></a>' .
    '<button type="submit" form="companyForm" class="ss-btn ss-btn-gradient"><i class="fas fa-save"></i> <span class="d-none d-md-inline">Save Changes</span></button>'
) ?>

<!-- ============== COMPANY HERO BANNER ============== -->
<div class="ss-card mb-4 ss-animate-fade-up" style="overflow:hidden;">
    <div style="height:160px;background:var(--ss-grad-primary);position:relative;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 50%);"></div>
        <div style="position:absolute;inset:0;opacity:0.15;background-image:radial-gradient(circle at 2px 2px, rgba(255,255,255,0.4) 1px, transparent 0);background-size:24px 24px;"></div>
    </div>
    <div class="ss-card-body" style="margin-top:-60px;position:relative;z-index:2;padding-top:0;">
        <div class="d-flex flex-wrap align-items-end gap-4">
            <!-- Logo -->
            <div style="width:120px;height:120px;border-radius:var(--ss-r-lg);background:var(--ss-surface);border:4px solid var(--ss-surface);box-shadow:var(--ss-shadow-md);overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <?php if ($logo): ?>
                    <img src="<?= URL::asset($logo) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <div style="font-size:3rem;font-weight:800;color:var(--ss-primary);"><?= htmlspecialchars($initial) ?></div>
                <?php endif; ?>
            </div>
            <div style="flex:1;min-width:240px;padding-bottom:0.5rem;">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h2 style="margin:0;font-size:1.5rem;"><?= htmlspecialchars($companyName) ?></h2>
                    <?php if (!empty($employer['verified'])): ?>
                        <span class="ss-badge ss-badge-success ss-badge-lg"><i class="fas fa-check-circle"></i> Verified</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap gap-3" style="font-size:0.85rem;color:var(--ss-text-2);">
                    <?php if ($industry): ?>
                    <span><i class="fas fa-industry text-primary me-1"></i> <?= htmlspecialchars($industry) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($employer['company_size'])): ?>
                    <span><i class="fas fa-users text-primary me-1"></i> <?= htmlspecialchars($employer['company_size']) ?> employees</span>
                    <?php endif; ?>
                    <?php if (!empty($employer['location'])): ?>
                    <span><i class="fas fa-map-marker-alt text-primary me-1"></i> <?= htmlspecialchars($employer['location']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($employer['founded_year'])): ?>
                    <span><i class="fas fa-calendar text-primary me-1"></i> Founded <?= (int)$employer['founded_year'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($employer['website'])): ?>
            <a href="<?= htmlspecialchars($employer['website']) ?>" target="_blank" rel="noopener" class="ss-btn ss-btn-soft ss-btn-sm" style="margin-bottom:0.5rem;">
                <i class="fas fa-globe"></i> Visit Website
            </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($employer['description'])): ?>
        <div class="mt-4 p-3 rounded" style="background:var(--ss-surface-2);font-size:0.875rem;color:var(--ss-text-2);line-height:1.6;">
            <?= nl2br(htmlspecialchars($employer['description'])) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============== EDIT FORM ============== -->
<form id="companyForm" method="POST" action="<?= URL::to('employer/company/update') ?>" enctype="multipart/form-data" data-validate>
    <?= $csrfField ?? '' ?>
    <div class="row g-4">
        <!-- LEFT — main info -->
        <div class="col-lg-8">
            <div class="ss-card mb-4 ss-animate-fade-up">
                <div class="ss-card-header">
                    <h3><i class="fas fa-building text-primary"></i> Company Information</h3>
                </div>
                <div class="ss-card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="ss-form-group ss-float">
                                <input type="text" id="company_name" name="company_name" class="ss-input" placeholder=" " required
                                       value="<?= htmlspecialchars($employer['company_name'] ?? '') ?>">
                                <label for="company_name">Company Name <span class="req">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="ss-form-group ss-float">
                                <input type="number" id="founded_year" name="founded_year" class="ss-input" min="1900" max="<?= date('Y') ?>" placeholder=" "
                                       value="<?= htmlspecialchars($employer['founded_year'] ?? '') ?>">
                                <label for="founded_year">Founded Year</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="industry">Industry</label>
                                <input type="text" id="industry" name="industry" class="ss-input" list="industryList"
                                       value="<?= htmlspecialchars($employer['industry'] ?? '') ?>" placeholder="e.g. Information Technology">
                                <datalist id="industryList">
                                    <option value="Information Technology">
                                    <option value="Finance &amp; Banking">
                                    <option value="Healthcare">
                                    <option value="Education">
                                    <option value="Agriculture">
                                    <option value="Manufacturing">
                                    <option value="Telecommunications">
                                    <option value="Retail &amp; E-commerce">
                                    <option value="Construction">
                                    <option value="Media &amp; Marketing">
                                    <option value="Energy">
                                    <option value="Transportation &amp; Logistics">
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="company_size">Company Size</label>
                                <select id="company_size" name="company_size" class="ss-select">
                                    <option value="">Select size...</option>
                                    <?php foreach ($sizes as $s): ?>
                                        <option value="<?= htmlspecialchars($s) ?>" <?= ($employer['company_size'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?> employees</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="location">Location</label>
                                <div class="ss-input-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input type="text" id="location" name="location" class="ss-input"
                                           value="<?= htmlspecialchars($employer['location'] ?? '') ?>" placeholder="e.g. Kigali, Rwanda">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="ss-form-group">
                                <label class="ss-form-label" for="website">Website</label>
                                <div class="ss-input-icon">
                                    <i class="fas fa-globe"></i>
                                    <input type="url" id="website" name="website" class="ss-input"
                                           value="<?= htmlspecialchars($employer['website'] ?? '') ?>" placeholder="https://">
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="ss-form-group mb-0">
                                <label class="ss-form-label" for="description">Company Description</label>
                                <textarea id="description" name="description" class="ss-textarea" placeholder="Tell candidates about your mission, culture, and what makes your company a great place to work..."><?= htmlspecialchars($employer['description'] ?? '') ?></textarea>
                                <div class="ss-form-hint">Aim for 2–3 paragraphs. This is your chance to sell your employer brand.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT — logo upload + save -->
        <div class="col-lg-4">
            <div class="ss-card mb-4 ss-animate-fade-up" style="position:sticky;top:90px;">
                <div class="ss-card-header">
                    <h3><i class="fas fa-image text-primary"></i> Company Logo</h3>
                </div>
                <div class="ss-card-body text-center">
                    <div id="logoPreview" style="width:140px;height:140px;border-radius:var(--ss-r-lg);background:var(--ss-grad-cool);margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid var(--ss-border);">
                        <?php if ($logo): ?>
                            <img src="<?= URL::asset($logo) ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="font-size:3rem;font-weight:800;color:#fff;"><?= htmlspecialchars($initial) ?></div>
                        <?php endif; ?>
                    </div>

                    <label class="ss-file-upload" for="company_logo" style="cursor:pointer;margin-bottom:0.75rem;">
                        <input type="file" id="company_logo" name="company_logo" accept="image/*" data-file-preview="#logoPreview" hidden>
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Drop logo here or click to upload</div>
                        <div class="upload-hint">PNG, JPG up to 2MB · Square recommended</div>
                    </label>

                    <div class="text-start p-3 rounded" style="background:var(--ss-surface-2);font-size:0.78rem;color:var(--ss-text-2);">
                        <div class="fw-semibold mb-1" style="color:var(--ss-text);"><i class="fas fa-info-circle text-primary me-1"></i> Logo tips</div>
                        <ul style="margin:0;padding-left:1.1rem;">
                            <li>Use a square image (≥ 256×256)</li>
                            <li>Transparent PNGs work best</li>
                            <li>Your logo appears on every job listing</li>
                        </ul>
                    </div>
                </div>
                <div class="ss-card-footer">
                    <button type="submit" class="ss-btn ss-btn-gradient ss-btn-block"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- ============== QUICK STATS SUMMARY ============== -->
<div class="ss-stats-grid mt-2">
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-primary"><i class="fas fa-briefcase"></i></div>
        <div class="stat-value">—</div>
        <div class="stat-label">Active Jobs</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-success"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-value">—</div>
        <div class="stat-label">Internships</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-info"><i class="fas fa-laptop-code"></i></div>
        <div class="stat-value">—</div>
        <div class="stat-label">Freelance</div>
    </div>
    <div class="ss-stat-card ss-card-hover">
        <div class="stat-icon bg-soft-warning"><i class="fas fa-users"></i></div>
        <div class="stat-value">—</div>
        <div class="stat-label">Total Hires</div>
    </div>
</div>
